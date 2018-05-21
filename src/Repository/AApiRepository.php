<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

use Miloshavlicek\DoctrineApiMapper\ACLEntity\AACL;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

abstract class AApiRepository extends ServiceEntityRepository
{
    use TApiUserRepository;
    use TPropertiesListEntity;

    /** @var AACL */
    protected $acl;

    /** @var array */
    protected $joins = [];

    public function __construct(ManagerRegistry $registry, string $entityClass, string $acl)
    {
        parent::__construct($registry, $entityClass);
        $this->acl = new $acl;
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

}