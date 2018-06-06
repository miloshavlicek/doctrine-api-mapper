<?php

namespace Miloshavlicek\DoctrineApiMapper\Service;

use Miloshavlicek\DoctrineApiMapper\EntityFilter\EntityFilter;
use Miloshavlicek\DoctrineApiMapper\Mapper\MapperFactory;
use Miloshavlicek\DoctrineApiMapper\Solver;

class EntityFilterFactory
{

    public function create(array $filterFields, ?string $acl = null, $filterOperator = null): EntityFilter
    {
        $filter = new EntityFilter;

        $filter->setFilter($filterFields);
        $filterOperator && $filter->setFilterOperator($filterOperator);
        $acl && $filter->setAcl(new $acl());

        return $filter;
    }

}