<?php

namespace Miloshavlicek\DoctrineApiMapper\Params;

use FOS\RestBundle\Request\ParamFetcherInterface;

interface IParams
{

    public function __construct(ParamFetcherInterface $paramFetcher);

    public function init(string $schema): void;

    public function getEntity(): string;

    public function setEntity(string $entity);

}