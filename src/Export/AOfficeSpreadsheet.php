<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

use Miloshavlicek\DoctrineApiMapper\Exception\BadRequestException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class AOfficeSpreadsheet extends AExport
{

    /** @var string */
    protected $contentType = 'application/vnd.ms-excel';

    /** @var string */
    protected $extension = '.xlsx';

    /** @var string */
    protected $format = 'Xlsx';

    public function __construct(array $data)
    {
        $this->isSupported();
        $this->data = $data;
    }

    public function isSupported()
    {
        parent::isSupported();
        if (!class_exists(Spreadsheet::class)) {
            throw new BadRequestException('Format valid, but not Spreadsheet implemented!');
        }
    }

    public function generateFile()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $rowI = 0;
        foreach ($this->data as $line) {
            // TODO: fix array of array (now ignored)
            $line = array_filter($line, function ($val) {
                return !is_array($val);
            });

            // header
            if ($rowI === 0) {
                $fields = [];

                $colI = 0;
                foreach ($line as $colKey => $col) {
                    $sheet->setCellValueByColumnAndRow($rowI, $colI, $colKey);
                    $colI++;
                }

                $rowI++;
            }

            $colI = 0;
            foreach ($line as $colKey => $col) {
                $sheet->setCellValueByColumnAndRow($rowI, $colI, $col);
                $colI++;
            }

            $rowI++;
        }

        $writer = IOFactory::createWriter($spreadsheet, $this->format);

        $this->setHeaders();
        $writer->save("php://output");
        exit;
    }

}