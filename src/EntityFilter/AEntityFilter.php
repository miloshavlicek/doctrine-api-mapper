<?php

namespace Miloshavlicek\DoctrineApiMapper\EntityFilter;

use Miloshavlicek\DoctrineApiMapper\ACLEntity\AACL;
use Miloshavlicek\DoctrineApiMapper\ACLEntity\BlankACL;

abstract class AEntityFilter
{

    /** @var AACL */
    protected $acl;

    public function __construct()
    {
        $this->acl = new BlankACL();
    }

    public function getACL()
    {
        return new BlankACL();
    }

}