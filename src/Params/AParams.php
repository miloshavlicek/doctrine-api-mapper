<?php

namespace Miloshavlicek\DoctrineApiMapper\Params;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Miloshavlicek\DoctrineApiMapper\ACLEntity\AACL;
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

    /** @var AACL */
    private $acl;

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
        $this->setAcl($repository->getAcl());
    }

    /**
     * @param string $prefix
     */
    protected function attachAllRepositoryReadPropertiesToUrl(string $prefix = ''): void
    {
        $read = $this->getAcl()->getEntityReadProperties($this->getUserRoles());
        $joins = $this->getAcl()->getEntityJoinsPermissions($this->getUserRoles());

        $this->attachPropertiesToUrl(array_merge($read, $joins), $prefix);
    }

    public function getAcl(): ?AACL
    {
        return $this->acl;
    }

    public function setAcl(?AACL $acl): void
    {
        if ($this->acl) {
            /** hotfix TODO: solve */
            return;
        }

        $this->acl = $acl;
    }

    protected function getUserRoles(): array
    {
        return $this->user ? $this->user->getRoles() : [];
    }

    /**
     * @param array $properties
     * @param string $prefix
     */
    protected function attachPropertiesToUrl(array $properties, string $prefix = ''): void
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
    protected function attachAllRepositoryWritePropertiesToUrl(string $prefix = ''): void
    {
        $read = $this->getAcl()->getEntityReadProperties($this->getUserRoles());
        $joins = $this->getAcl()->getEntityJoinsPermissions($this->getUserRoles());

        $this->attachPropertiesToUrl(array_merge($read, $joins), $prefix);
    }

}