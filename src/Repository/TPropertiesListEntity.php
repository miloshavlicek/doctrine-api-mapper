<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

use Miloshavlicek\DoctrineApiMapper\Exception\AccessDeniedException;

trait TPropertiesListEntity
{

    public function getEntityReadProperties(array $acls = []): array
    {
        $acls = $this->aclsDefault($acls);

        $out = [];
        foreach ($acls as $acl) {
            $out = array_merge($out, $acl->getEntityReadProperties($this->getUserRoles()));
        }

        return $out;
    }

    private function aclsDefault(array $acls = []): array
    {
        if (count($acls) === 0) {
            $acls['*'] = $this->acl;
        }

        return $acls;
    }

    private function getUserRoles(): array
    {
        return $this->user ? $this->user->getRoles() : [];
    }

    public function getEntityWriteProperties(array $acls = []): array
    {
        $acls = $this->aclsDefault($acls);

        $out = [];
        foreach ($acls as $acl) {
            $out = array_merge($out, $acl->getEntityWriteProperties($this->getUserRoles()));
        }

        return $out;
    }

    public function getEntityJoin(string $property, array $acls = []): IApiRepository
    {
        $acls = $this->aclsDefault($acls);

        if ($this->hasEntityJoin($property) && $this->hasPermissionEntityJoin($property, $acls)) {
            return $this->joins[$property];
        } else {
            throw new AccessDeniedException('Insufficient rights for join.');
        }
    }

    public function hasEntityJoin(string $property): bool
    {
        return !empty($this->joins[$property]);
    }

    public function hasPermissionEntityJoin(string $property, array $acls = []): bool
    {
        $acls = $this->aclsDefault($acls);

        foreach ($acls as $acl) {
            if ($acl->checkEntityJoin($this->user->getRoles(), $property)) {
                return true;
            }
        }

        return false;
    }

}