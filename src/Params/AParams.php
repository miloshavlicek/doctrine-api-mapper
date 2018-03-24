<?php

namespace Miloshavlicek\DoctrineApiMapper\Params;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcherInterface;

abstract class AParams
{

    /** @var bool */
    protected $isRequest = false;

    /** @var string */
    protected $entity;

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
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     */
    public function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @param string $prefix
     */
    protected function attachAllEntityReadPropertiesToUrl(string $prefix = ''): void
    {
        $this->attachPropertiesToUrl($this->entity::getEntityReadProperties(), $prefix);
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
    protected function attachAllEntityWritePropertiesToUrl(string $prefix = ''): void
    {
        $this->attachPropertiesToUrl($this->entity::getEntityWriteProperties(), $prefix);
    }

}