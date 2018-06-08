<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Html extends AOfficeSpreadsheet
{

    /** @var string */
    protected $contentType = 'text/html';

    /** @var string */
    protected $extension = '.html';

    /** @var string */
    protected $format = 'Html';

}