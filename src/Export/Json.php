<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

class Json extends AExport implements IExport
{

    /** @var string */
    protected $extension = '.json';
    /** @var string */
    protected $contentType = 'application/json';

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function generateFile()
    {
        $temp_memory = fopen('php://memory', 'w');

        fputs($temp_memory, json_encode($this->data));

        fseek($temp_memory, 0);

        $this->setHeaders();
        fpassthru($temp_memory);
        exit;
    }

}