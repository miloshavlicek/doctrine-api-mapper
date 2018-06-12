<?php

namespace Miloshavlicek\DoctrineApiMapper\ACLEntity;

use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;

class AACL
{
    protected $properties = [];
    protected $joins = [];
    private $permissions = [
        'full' => [],
        'read' => [],
        'write' => [],
        'join' => [],
        'delete' => false,
        'create' => false
    ];

    public function __construct()
    {

    }

    public function getEntityReadProperties(array $roles = []): array
    {
        return array_unique($this->solveAcl(['full', 'read'], $roles));
    }

    private function solveAcl(array $permissions, array $roles): array
    {
        $a0 = [];
        $roles[] = '*';
        foreach ($roles as $role) {
            $a1 = [];
            foreach ($permissions as $permission) {
                if (!isset($this->permissions[$permission])) {
                    throw new InternalException(sprintf('Permission %s not available.', $permission));
                }

                if (isset($this->permissions[$permission][$role])) {
                    if (in_array('*', $this->permissions[$permission][$role])) {
                        $a1 = array_merge($a1, $permission === 'join' ? $this->joins : array_merge($this->properties, $this->joins));
                    } else {
                        $a1 = array_merge($a1, $this->permissions[$permission][$role]);
                    }
                }
            }
            $a0 = array_merge($a0, array_merge($a1));
        }

        return array_merge($a0);
    }

    public function getEntityWriteProperties(array $roles = []): array
    {
        return array_unique($this->solveAcl(['full', 'write'], $roles));
    }

    public function getEntityCreatePermission(array $roles = []): bool
    {
        return $this->solveAclCreate($roles);
    }

    private function solveAclCreate(array $roles): bool
    {
        $roles[] = '*';
        foreach ($roles as $role) {
            if (isset($this->permissions['create'][$role]) && $this->permissions['create'][$role] === true) {
                return true;
            }
        }
        return false;
    }

    public function getEntityDeletePermission(array $roles = []): bool
    {
        return $this->solveAclDelete($roles);
    }

    private function solveAclDelete(array $roles): bool
    {
        $roles[] = '*';
        foreach ($roles as $role) {
            if (isset($this->permissions['delete'][$role]) && $this->permissions['delete'][$role] === true) {
                return true;
            }
        }
        return false;
    }

    public function getEntityJoinsPermissions(array $roles = []): array
    {
        return $this->solveAcl(['join'], $roles);
    }

    public function checkEntityJoin(array $roles = [], string $property): bool
    {
        if (!in_array($property, $this->joins)) {
            return false;
            // throw new InternalException(sprintf('Join "%s" not found!', $property));
        }

        return in_array($property, $this->solveAcl(['join'], $roles));
    }

    /**
     * @param string|array $roles
     */
    protected function appendFullPermissions($roles)
    {
        $this->append('full', $roles, ['*']);
        $this->append('create', $roles, true);
        $this->append('delete', $roles, true);
        $this->append('join', $roles, ['*']);
    }

    /**
     * @param string $permission
     * @param string|array $roles
     * @param $value
     */
    protected function append(string $permission, $roles, $value)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            $this->appendOne($permission, $role, $value);
        }
    }

    /**
     * @param string $permission
     * @param string $role
     * @param $value
     * @throws InternalException
     */
    protected function appendOne(string $permission, string $role, $value)
    {
        if (!isset($this->permissions[$permission])) {
            throw new InternalException(sprintf('Permission %s not available for append.', $permission));
        }

        if (!isset($this->permissions[$permission][$role])) {
            $this->permissions[$permission][$role] = [];
        }

        if (in_array($permission, ['create', 'delete'])) {
            $this->permissions[$permission][$role] = $value;
        } else {
            $this->permissions[$permission][$role] = array_merge($this->permissions[$permission][$role], $value);
        }
    }

}