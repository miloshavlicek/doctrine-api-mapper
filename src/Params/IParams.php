<?php

namespace Miloshavlicek\DoctrineApiMapper\Params;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Miloshavlicek\DoctrineApiMapper\Repository\IApiRepository;

interface IParams
{

    public function __construct(ParamFetcherInterface $paramFetcher, $user);

    public function init(string $schema): void;

    public function getRepository(): ?IApiRepository;

    public function setRepository(IApiRepository $entity);

}