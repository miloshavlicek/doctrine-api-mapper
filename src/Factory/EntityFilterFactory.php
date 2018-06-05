<?php

namespace Miloshavlicek\DoctrineApiMapper\Factory;

use Miloshavlicek\DoctrineApiMapper\Mapper\MapperFactory;
use Miloshavlicek\DoctrineApiMapper\Solver;

class EntityFilterFactory extends AEntityFilter
{

    public function __construct(array $filter, ?string $acl = null, $filterOperator = null)
    {
        $this->filter = $filter;
        $filterOperator && $this->filterOperator = $this->setFilterOperator($filterOperator);
        $acl && $this->setAcl(new $acl());
    }

}