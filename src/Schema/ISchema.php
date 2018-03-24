<?php

namespace Miloshavlicek\DoctrineApiMapper\Schema;

interface ISchema {

    public function mapOutput(array $data): array;

}