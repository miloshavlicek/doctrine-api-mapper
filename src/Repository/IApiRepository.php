<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface IApiRepository
{
    public function __construct(RegistryInterface $registry);

    public function create();

    public function add($entity);

    public function getUser(): ?UserInterface;

    public function setUser(?UserInterface $user);

}