<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

interface IPropertiesListEntity
{
    public function getEntityReadProperties();

    public function getEntityWriteProperties();

    public function getEntityJoin(string $property): IApiRepository;

}