<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

use Symfony\Component\Security\Core\User\UserInterface;

trait TApiUserRepository
{
    /** @var UserInterface */
    protected $user;

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