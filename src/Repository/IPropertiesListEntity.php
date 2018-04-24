<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

interface IPropertiesListEntity
{
    public static function getEntityReadProperties();

    public static function getEntityWriteProperties();

    public static function getEntityJoin(string $property): string;

}