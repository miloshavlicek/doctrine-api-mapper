<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

class Xlsx extends AOfficeSpreadsheet implements IExport
{

    /** @var string */
    protected $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /** @var string */
    protected $extension = '.xlsx';

    /** @var string */
    protected $format = 'Xlsx';

}