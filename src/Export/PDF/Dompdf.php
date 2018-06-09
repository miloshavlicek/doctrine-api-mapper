<?php

namespace Miloshavlicek\DoctrineApiMapper\Export\PDF;

use Miloshavlicek\DoctrineApiMapper\Exception\BadRequestException;
use Miloshavlicek\DoctrineApiMapper\Export\IExport;

class Dompdf extends APdf implements IExport
{

    /** @var string */
    protected $format = 'Dompdf';

    public function isSupported()
    {
        parent::isSupported();
        if (!class_exists(\Dompdf\Dompdf::class)) {
            throw new BadRequestException('Dompdf not implemented!');
        }
    }

}