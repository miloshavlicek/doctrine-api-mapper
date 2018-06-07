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

        return array_unique($out);
    }

    private function aclsDefault(array $acls = []): array
    {
        if ($acls === []) {
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

        return array_unique($out);
    }

    public function getEntityJoins(array $acls = []): array
    {
        $joins = $this->getEntityJoinsPermissions($acls);

        $out = [];
        foreach ($joins as $join) {
            $out[$join] = $this->getEntityJoin($join, $acls);
        }

        return $out;
    }

    public function getEntityJoinsPermissions(array $acls = []): array
    {
        $acls = $this->aclsDefault($acls);

        $out = [];
        foreach ($acls as $acl) {
            $out = array_merge($out, $acl->getEntityJoinsPermissions($this->getUserRoles()));
        }
        return array_unique($out);
    }

    public function getEntityJoin(string $property, array $acls = []): ?IApiRepository
    {
        $acls = $this->aclsDefault($acls);

        if ($this->hasEntityJoin($property) && $this->hasPermissionEntityJoin($property, $acls)) {
            return $this->joins[$property];
        } elseif (!$this->hasEntityJoin($property)) {
            // Join repository not speficied
            return null;
        } else {
            throw new AccessDeniedException(sprintf('Insufficient rights for join "%s".', $property));
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