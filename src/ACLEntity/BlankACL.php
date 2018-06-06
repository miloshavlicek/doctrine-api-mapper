<?php

namespace Miloshavlicek\DoctrineApiMapper\ACLEntity;

class BlankACL extends AACL
{

    public function __construct()
    {
        parent::__construct();
        $this->appendFullPermissions('SUPERADMIN');
    }

}