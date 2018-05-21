<?php

namespace Miloshavlicek\DoctrineApiMapper\Mapper;

use Miloshavlicek\DoctrineApiMapper\Entity\IPropertiesListEntity;

class ParamToEntityMethod
{

    /** @var IPropertiesListEntity */
    private $entity;

    /** @var array */
    private $params = [];

    /**
     * ParamToEntityMethod constructor.
     * @param $entity
     * @param array $params
     */
    public function __construct($entity, array $params)
    {
        $this->entity = $entity;
        $this->params = $params;
    }

    /**
     * @param string $str
     * @param string $prefix
     * @return string
     */
    public static function untranslate(string $str, string $prefix = '')
    {
        $str = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));

        if ($prefix !== '' && substr($str, 0, strlen($prefix)) == $prefix) {
            $str = substr($str, strlen($prefix));
        }

        return $str;
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function resolveGet(string $prefix = 'get')
    {
        $out = [];
        foreach ($this->params as $param) { // e.g. [event.organizers.id, event.id, event.title]
            $entity = $this->entity;
            $outOne = null;
            $outI = &$outOne;
            foreach (explode('.', $param) as $explode) { // e.g. [event, organizers, id]
                $outI[$explode] = null;
                $outI = &$outI[$explode];

                if ($entity === null) {
                    break;
                }
                $method = self::translate($explode, $prefix);
                $entity = $entity->$method();
            }

            $outI = $entity;
            $out = array_merge_recursive($out, $outOne);
        }
        return $out;
    }

    /**
     * @param string $str
     * @param string $prefix
     * @return string
     */
    public static function translate(string $str, string $prefix = '')
    {
        return lcfirst($prefix . ucfirst(str_replace('_', '', ucwords($str, '_'))));
    }

    /**
     * @param $values
     * @param string $prefix
     * @return mixed
     */
    public function resolveSet(array $values, string $prefix = 'set')
    {
        foreach ($this->params as $param) {
            $method = self::translate($param, $prefix);
            if (isset($values[$param])) {
                $this->entity->$method($values[$param]);
            }
        }
        return $this->entity;
    }

}