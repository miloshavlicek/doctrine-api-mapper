<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

use App\ACLEntity\AACL;

trait TPropertiesListEntity
{

    public function getEntityReadProperties(?AACL $acl = null): array
    {
        if (!$acl) {
            $acl = $this->acl;
        }
        return $acl->getEntityReadProperties($this->getUserRoles());
    }

    private function getUserRoles(): array
    {
        return $this->user ? $this->user->getRoles() : [];
    }

    public function getEntityWriteProperties(?AACL $acl = null): array
    {
        if (!$acl) {
            $acl = $this->acl;
        }
        return $acl->getEntityWriteProperties($this->getUserRoles());
    }

    public function getEntityJoin(string $property, ?AACL $acl = null): IApiRepository
    {
        if (!$acl) {
            $acl = $this->acl;
        }
        if ($this->hasEntityJoin($property) && $this->hasPermissionEntityJoin($property)) {
            return $this->joins[$property];
        }
    }

    public function hasEntityJoin(string $property): bool
    {
        return !empty($this->joins[$property]);
    }

    public function hasPermissionEntityJoin(string $property, ?AACL $acl = null): bool
    {
        if (!$acl) {
            $acl = $this->acl;
        }
        return $acl->checkEntityJoin($this->user->getRoles(), $property);
    }

}