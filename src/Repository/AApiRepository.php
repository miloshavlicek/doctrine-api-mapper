<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Miloshavlicek\DoctrineApiMapper\ACLEntity\AACL;
use Miloshavlicek\DoctrineApiMapper\EntityFilter\EntityFilter;
use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;
use Miloshavlicek\DoctrineApiMapper\Service\EntityFilterFactory;

abstract class AApiRepository extends ServiceEntityRepository implements IApiRepository, IPropertiesListEntity
{
    use TApiUserRepository;
    use TPropertiesListEntity;

    /** @var AACL */
    protected $acl;

    /** @var array */
    protected $joins = [];
    /** @var EntityFilterFactory */
    protected $filterFactory;
    /** @var array */
    private $filters = [];
    /** @var bool */
    private $filtersInitialized = false;

    public function __construct(ManagerRegistry $registry, EntityFilterFactory $filterFactory, string $entityClass, string $acl)
    {
        parent::__construct($registry, $entityClass);
        $this->acl = new $acl;
        $this->filterFactory = $filterFactory;
    }

    public function getAcl(): AACL
    {
        return $this->acl;
    }

    public function create()
    {
        return (new $this->_entityName);
    }

    public function add($entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    /**
     * @param string|int $name
     * @return EntityFilter|null
     */
    public function getFilter($name): ?EntityFilter
    {
        !$this->filtersInitialized && $this->initFilters();

        if (isset($this->filters[$name])) {
            return $this->filters[$name];
        }
        return null;
    }

    protected function initFilters()
    {
        $this->filtersInitialized = true;
    }

    /**
     * @param int|string $name
     * @param EntityFilter $filter
     * @param bool $overwrite
     * @throws InternalException
     */
    protected function addFilter($name, EntityFilter $filter, bool $overwrite = false)
    {
        if (!$overwrite && isset($this->filters[$name])) {
            throw new InternalException('Filter already exists!');
        }

        $this->filters[$name] = $filter;
    }

}