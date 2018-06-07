<?php

namespace Miloshavlicek\DoctrineApiMapper\Params;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Miloshavlicek\DoctrineApiMapper\ACLEntity\AACL;
use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;

abstract class AParams
{

    /** @var bool */
    protected $isRequest = false;

    /** @var string */
    protected $repository;

    /** @var ParamFetcherInterface */
    protected $paramFetcher;

    /** @var bool */
    protected $initialized = false;

    /** @var string */
    protected $schema;

    /** @var AACL[] */
    private $acls = [];

    private $user;

    public function __construct(ParamFetcherInterface $paramFetcher, $user)
    {
        $this->paramFetcher = $paramFetcher;
        $this->user = $user;
    }

    /**
     * @param string $schema
     */
    public function init(string $schema): void
    {
        if ($this->initialized) {
            return;
        }

        $this->schema = $schema;

        $this->initIt();

        $this->initialized = true;
    }

    abstract protected function initIt(): void;

    /**
     * @return string
     */
    public function getRepository(): ?IApiRepository
    {
        return $this->repository;
    }

    /**
     * @param IApiRepository $entity
     */
    public function setRepository(IApiRepository $repository): void
    {
        $this->repository = $repository;
        $this->acls['*'] = $repository->getAcl();
    }

    /**
     * @param string|int $filterName
     * @param AACL|null $acl
     * @param bool $overwrite
     * @throws InternalException
     */
    public function setAcl($filterName, ?AACL $acl, bool $overwrite = false): void
    {
        if (!$overwrite && isset($this->acls[$filterName])) {
            throw new InternalException('ACL with the same name already set.');
        }

        $this->acls[$filterName] = $acl;
    }

    /**
     * @param string $prefix
     */
    protected function attachAllRepositoryReadPropertiesToUrl(string $prefix): void
    {
        $read = [];
        $joins = [];
        foreach ($this->acls as $acl) {
            $read = array_merge($read, $acl->getEntityReadProperties($this->getUserRoles()));
            $joins = array_merge($joins, $acl->getEntityJoinsPermissions($this->getUserRoles()));
        }

        $this->attachPropertiesToUrl(array_merge($read, $joins), $prefix);
    }

    protected function getUserRoles(): array
    {
        return $this->user ? $this->user->getRoles() : [];
    }

    /**
     * @param array $properties
     * @param string $prefix
     */
    private function attachPropertiesToUrl(array $properties, string $prefix = ''): void
    {
        // Query = GET, Request = POST/DELETE/PUT/PATCH
        foreach ($properties as $property) {
            $dynamicParam = $this->isRequest() ? new RequestParam() : new QueryParam();
            $dynamicParam->name = $prefix . $property;
            $dynamicParam->nullable = true;
            $this->paramFetcher->addParam($dynamicParam);
        }
    }

    /**
     * @return bool
     */
    protected function isRequest(): bool
    {
        return $this->isRequest;
    }

    /**
     * @param string $prefix
     */
    protected function attachAllRepositoryWritePropertiesToUrl(string $prefix): void
    {
        $write = [];
        $joins = [];
        foreach ($this->acls as $acl) {
            $write = array_merge($write, $acl->getEntityWriteProperties($this->getUserRoles()));
            $joins = array_merge($joins, $acl->getEntityJoinsPermissions($this->getUserRoles()));
        }

        $this->attachPropertiesToUrl(array_unique(array_merge($write, $joins)), $prefix);
    }

}