<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class AApiRepository extends ServiceEntityRepository
{

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