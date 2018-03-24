<?php
namespace Miloshavlicek\DoctrineApiMapper\Request;

interface IEntityRequest {

    /**
     * @return array
     */
    public function solve(): array;

}