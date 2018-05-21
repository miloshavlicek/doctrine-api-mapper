<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

trait TPropertiesListEntity
{

    public function getEntityReadProperties(): array
    {
        return $this->acl->getEntityReadProperties($this->getUserRoles());
    }

    private function getUserRoles(): array
    {
        return $this->user ? $this->user->getRoles() : [];
    }

    public function getEntityWriteProperties(): array
    {
        return $this->acl->getEntityWriteProperties($this->getUserRoles());
    }

    public function getEntityJoin(string $property): IApiRepository
    {
        if ($this->hasEntityJoin($property) && $this->hasPermissionEntityJoin($property)) {
            return $this->joins[$property];
        }
    }

    public function hasEntityJoin(string $property): bool
    {
        return !empty($this->joins[$property]);
    }

    public function hasPermissionEntityJoin(string $property): bool
    {
        return $this->acl->checkEntityJoin($this->user->getRoles(), $property);
    }

}