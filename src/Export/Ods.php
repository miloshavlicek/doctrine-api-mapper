<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Ods extends AOfficeSpreadsheet
{

    /** @var string */
    protected $contentType = 'application/vnd.oasis.opendocument.spreadsheet';

    /** @var string */
    protected $extension = '.ods';

    /** @var string */
    protected $format = 'Ods';

}