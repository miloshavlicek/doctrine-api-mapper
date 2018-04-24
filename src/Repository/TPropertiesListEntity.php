<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

trait TPropertiesListEntity
{

    public static function getEntityReadProperties()
    {
        return array_merge(
            isset(self::$properties) ? self::$properties : [],
            isset(self::$propertiesReadOnly) ? self::$propertiesReadOnly : []
        );
    }

    public static function getEntityWriteProperties()
    {
        return array_merge(
            isset(self::$properties) ? self::$properties : [],
            isset(self::$propertiesWriteOnly) ? self::$propertiesWriteOnly : []
        );
    }

    public static function getEntityJoin(string $property): string
    {
        if (property_exists(self, 'joins') && isset($joins[$property])) {
            return $joins[$property];
        }
        throw new \Exception('Join not found!');
    }

}