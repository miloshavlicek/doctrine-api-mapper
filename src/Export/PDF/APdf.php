<?php

namespace Miloshavlicek\DoctrineApiMapper\Export\PDF;

use Miloshavlicek\DoctrineApiMapper\Export\AOfficeSpreadsheet;

abstract class APdf extends AOfficeSpreadsheet
{

    /** @var string */
    protected $contentType = 'application/pdf';

    /** @var string */
    protected $extension = '.pdf';

}