<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

class Xlsx extends AOfficeSpreadsheet
{

    /** @var string */
    protected $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /** @var string */
    protected $extension = '.xlsx';

    /** @var string */
    protected $format = 'Xlsx';

}