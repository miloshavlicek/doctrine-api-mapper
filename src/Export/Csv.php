<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

class Csv extends AExport
{

    /** @var string */
    protected $extension = '.csv';
    /** @var string */
    protected $contentType = 'application/csv';
    /** @var string */
    private $delimiter;

    public function __construct(array $data, string $delimiter = ',')
    {
        $this->data = $data;
        $this->delimiter = $delimiter;
    }

    public function generateFile()
    {
        $temp_memory = fopen('php://memory', 'w');

        $rowI = 0;
        foreach ($this->data as $line) {
            // TODO: fix array of array (now ignored)
            $line = array_filter($line, function ($val) {
                return !is_array($val);
            });

            // header
            if ($rowI === 0) {
                $fields = [];

                foreach ($line as $colKey => $col) {
                    $fields[] = $colKey;
                }

                fputcsv($temp_memory, $fields, $this->delimiter);
            }

            fputcsv($temp_memory, $line, $this->delimiter);
            $rowI++;
        }

        fseek($temp_memory, 0);

        $this->setHeaders();
        fpassthru($temp_memory);
        exit;
    }

}