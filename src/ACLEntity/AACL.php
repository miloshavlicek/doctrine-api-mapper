<?php

namespace Miloshavlicek\DoctrineApiMapper\ACLEntity;

use Exception;

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
        return $this->solveACL(['full', 'read'], $roles);
    }

    private function solveACL(array $acls, array $roles): array
    {
        $a0 = [];
        $roles[] = '*';
        foreach ($roles as $role) {
            $a1 = [];
            foreach ($acls as $acl) {
                if (!isset($this->acls[$acl])) {
                    throw new Exception(sprintf('ACL %s not available.', $acl));
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
        return $this->solveACL(['full', 'write'], $roles);
    }

    public function getEntityDeletePermission(array $roles = []): bool
    {
        return $this->solveACLDelete($roles);
    }

    private function solveACLDelete(array $roles, bool $positiveFirst = false): bool
    {
        $roles[] = '*';
        foreach ($roles as $role) {
            if (isset($this->acls['delete'][$role])) {
                if ($this->acls['delete'][$role] === $positiveFirst) {
                    return $positiveFirst;
                } elseif ($this->acls['delete'][$role] === !$positiveFirst) {
                    return !$positiveFirst;
                }
            }
        }
        return $positiveFirst;
    }

    public function getEntityJoinsPermissions(array $roles = []): array
    {
        return $this->solveACL(['joins'], $roles);
    }

    public function checkEntityJoin(array $roles = [], string $property): bool
    {
        if (!in_array($property, $this->joins)) {
            throw new Exception(sprintf('Join "%s" not found!', $property));
        }

        return in_array($property, $this->solveACL(['joins'], $roles));
    }

    protected function appendToACL(string $acl, string $role, $value)
    {
        if (!isset($this->acls[$acl])) {
            throw new Exception(sprintf('ACL %s not available for append.', $acl));
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