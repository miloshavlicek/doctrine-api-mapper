<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Xls extends AOfficeSpreadsheet implements IExport
{

    /** @var string */
    protected $contentType = 'application/vnd.ms-excel';

    /** @var string */
    protected $extension = '.xls';

    /** @var string */
    protected $format = 'Xls';

}