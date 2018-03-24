<?php

namespace Miloshavlicek\DoctrineApiMapper\Entity;

interface IPropertiesListEntity
{
    public static function getEntityReadProperties();

    public static function getEntityWriteProperties();

}