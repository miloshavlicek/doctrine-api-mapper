<?php

namespace Miloshavlicek\DoctrineApiMapper\EntityFilter;

use Doctrine\ORM\QueryBuilder;
use Miloshavlicek\DoctrineApiMapper\ACLEntity\AACL;
use Miloshavlicek\DoctrineApiMapper\ACLEntity\BlankACL;
use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;

class EntityFilter implements IEntityFilter
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

    public function appendQb(QueryBuilder $qb, $sufix = '')
    {
        if (!empty($suffix)) {
            $suffix = '_' . $suffix;
        }

        $paramCounter = 1;
        $qbEq = [];
        foreach ($this->filter as $filterKey => $filterValue) {
            if (is_array($filterValue)) {
                $qbEq[] = $qb->expr()->in('e.' . $filterKey, ':filter_' . $paramCounter . $sufix);
            } else {
                $qbEq[] = $qb->expr()->eq('e.' . $filterKey, ':filter_' . $paramCounter . $sufix);
            }
            $qb->setParameter('filter_' . $paramCounter . $sufix, $filterValue);
            $paramCounter++;
        }

        if (count($qbEq)) {
            $qb->andWhere('(' . implode(' ' . $this->filterOperator . ' ', $qbEq) . ')');
        }
    }

    public function setFilter(array $filter)
    {
        $this->filter = $filter;
    }

    public function setFilterOperator(string $value)
    {
        if (!in_array($value, ['AND', 'OR'])) {
            throw new InternalException('Invalid value for setFilterOperator!');
        }
        $this->filterOperator = $value;
    }

    public function check($entity): bool
    {
        // OR => return on first match, if no one: not match
        // AND => return on first dismatch, if no one: match

        foreach ($this->filter as $filterKey => $filterValue) {
            if (!isset($entity->$filterKey)) {
                throw new InternalException('Invalid entity.');
            }

            if (is_array($filterValue) && in_array($entity->$filterKey, $filterValue)) {
                if ($this->filterOperator === 'OR') {
                    return true;
                }
            } elseif ($entity->$filterKey === $filterValue) {
                if ($this->filterOperator === 'OR') {
                    return true;
                }
            } else {
                // Does not match
                if ($this->filterOperator === 'AND') {
                    return false;
                }
            }
        }

        if ($this->filterOperator === 'OR') {
            return false;
        } elseif ($this->filterOperator === 'AND') {
            return true;
        } else {
            throw new InternalException('Unknown operator.');
        }
    }

}