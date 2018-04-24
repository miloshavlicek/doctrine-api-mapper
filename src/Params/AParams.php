<?php

namespace Miloshavlicek\DoctrineApiMapper\Params;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
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

    /**
     * AParams constructor.
     * @param ParamFetcherInterface $paramFetcher
     */
    public function __construct(ParamFetcherInterface $paramFetcher)
    {
        $this->paramFetcher = $paramFetcher;
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

        $dynamicParam = new RequestParam();
        $dynamicParam->name = 'token';
        $dynamicParam->nullable = true;
        $this->paramFetcher->addParam($dynamicParam);

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
    }

    /**
     * @param string $prefix
     */
    protected function attachAllRepositoryReadPropertiesToUrl(string $prefix = ''): void
    {
        $this->attachPropertiesToUrl($this->repository::getEntityReadProperties(), $prefix);
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
        $this->attachPropertiesToUrl($this->repository::getEntityWriteProperties(), $prefix);
    }

}