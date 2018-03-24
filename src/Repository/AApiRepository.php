<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AApiRepository extends ServiceEntityRepository
{
    /** @var UserInterface */
    protected $user;

    public function __construct(RegistryInterface $registry, $entity)
    {
        parent::__construct($registry, $entity);
    }

    public function create()
    {
        $entity = new $this->_entityName;
        return $entity;
    }

    public function add($entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface|null $user
     */
    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

}