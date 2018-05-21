<?php

namespace Miloshavlicek\DoctrineApiMapper\Repository;

interface IApiRepository
{
    
    public function create();

    public function add($entity);

}