<?php

namespace Miloshavlicek\DoctrineApiMapper\ACLEntity;

use Miloshavlicek\DoctrineApiMapper\Exception\InternalException;

abstract class AACL
{
    protected $properties = [];
    protected $joins = [];

    protected $acls = [
        'full' => [],
        'read' => [],
        'write' => [],
        'joins' => [],
        'delete' => []
    ];

    public function __construct()
    {

    }

    public function getEntityReadProperties(array $roles = []): array
    {
        return $this->solveAcl(['full', 'read'], $roles);
    }

    private function solveAcl(array $acls, array $roles): array
    {
        $a0 = [];
        $roles[] = '*';
        foreach ($roles as $role) {
            $a1 = [];
            foreach ($acls as $acl) {
                if (!isset($this->acls[$acl])) {
                    throw new InternalException(sprintf('ACL %s not available.', $acl));
                }

                if (isset($this->acls[$acl][$role])) {
                    if (in_array('*', $this->acls[$acl][$role])) {
                        $a1 = array_merge($a1, $acl === 'joins' ? $this->joins : $this->properties);
                    } else {
                        $a1 = array_merge($a1, $this->acls[$acl][$role]);
                    }
                }
            }
            $a0 = array_merge($a0, array_merge($a1));
        }

        return array_merge($a0);
    }

    public function getEntityWriteProperties(array $roles = []): array
    {
        return $this->solveAcl(['full', 'write'], $roles);
    }

    public function getEntityDeletePermission(array $roles = []): bool
    {
        return $this->solveAclDelete($roles);
    }

    private function solveAclDelete(array $roles): bool
    {
        $roles[] = '*';
        foreach ($roles as $role) {
            if (isset($this->acls['delete'][$role]) && $this->acls['delete'][$role] === true) {
                return true;
            }
        }
        return false;
    }

    public function getEntityJoinsPermissions(array $roles = []): array
    {
        return $this->solveAcl(['joins'], $roles);
    }

    public function checkEntityJoin(array $roles = [], string $property): bool
    {
        if (!in_array($property, $this->joins)) {
            throw new InternalException(sprintf('Join "%s" not found!', $property));
        }

        return in_array($property, $this->solveAcl(['joins'], $roles));
    }

    /**
     * @param string|array $roles
     */
    protected function appendFullPermissions($roles)
    {
        $this->append('full', $roles, ['*']);
        $this->append('delete', $roles, true);
        $this->append('joins', $roles, ['*']);
    }

    /**
     * @param string $acl
     * @param string|array $roles
     * @param $value
     */
    protected function append(string $acl, $roles, $value)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            $this->appendOne($acl, $role, $value);
        }
    }

    /**
     * @param string $acl
     * @param string $role
     * @param $value
     * @throws InternalException
     */
    protected function appendOne(string $acl, string $role, $value)
    {
        if (!isset($this->acls[$acl])) {
            throw new InternalException(sprintf('ACL %s not available for append.', $acl));
        }

        if (!isset($this->acls[$acl][$role])) {
            $this->acls[$acl][$role] = [];
        }

        if ($acl === 'delete') {
            $this->acls[$acl][$role] = $value;
        } else {
            $this->acls[$acl][$role] = array_merge($this->acls[$acl][$role], $value);
        }
    }

}