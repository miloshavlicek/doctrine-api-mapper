<?php

namespace Miloshavlicek\DoctrineApiMapper\EntityFilter;

use Doctrine\ORM\QueryBuilder;
use Miloshavlicek\DoctrineApiMapper\ACLEntity\AACL;
use Miloshavlicek\DoctrineApiMapper\ACLEntity\BlankACL;
use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;

abstract class AEntityFilter implements IEntityFilter
{

    /** @var AACL */
    protected $acl;
    /** @var string */
    protected $filterOperator = 'AND';
    /** @var array */
    protected $filter = [];

    public function __construct()
    {
        $this->acl = new BlankACL();
    }

    public function getAcl()
    {
        return $this->acl;
    }

    public function setAcl(AACL $acl)
    {
        $this->acl = $acl;
    }

    public function appendQb(QueryBuilder $qb)
    {
        $paramCounter = 1;
        $qbEq = [];
        foreach ($this->filter as $filterKey => $filterValue) {
            if (is_array($filterValue)) {
                $qbEq[] = $qb->expr()->in('e.' . $filterKey, ':filter_' . $paramCounter);
            } else {
                $qbEq[] = $qb->expr()->eq('e.' . $filterKey, ':filter_' . $paramCounter);
            }
            $qb->setParameter('filter_' . $paramCounter, $filterValue);
            $paramCounter++;
        }

        if (count($qbEq)) {
            $qb->andWhere('(' . implode(' ' . $this->filterOperator . ' ', $qbEq) . ')');
        }
    }

    protected function setFilterOperator(string $value)
    {
        if (!in_array($value, ['AND', 'OR'])) {
            throw new InternalException('Invalid value for setFilterOperator!');
        }
        $this->filterOperator = $value;
    }

}