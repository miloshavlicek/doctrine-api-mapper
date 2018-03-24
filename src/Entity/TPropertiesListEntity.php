<?php

namespace Miloshavlicek\DoctrineApiMapper\Entity;

trait TPropertiesListEntity
{

    public static function getEntityReadProperties() {
        return array_merge(
            isset(self::$properties) ? self::$properties : [],
            isset(self::$propertiesReadOnly) ? self::$propertiesReadOnly : []
        );
    }

    public static function getEntityWriteProperties() {
        return array_merge(
            isset(self::$properties) ? self::$properties : [],
            isset(self::$propertiesWriteOnly) ? self::$propertiesWriteOnly : []
        );
    }

}