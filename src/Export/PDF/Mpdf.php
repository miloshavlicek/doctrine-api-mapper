<?php

namespace Miloshavlicek\DoctrineApiMapper\Export\PDF;

use Miloshavlicek\DoctrineApiMapper\Exception\BadRequestException;
use Miloshavlicek\DoctrineApiMapper\Export\IExport;

class Mpdf extends APdf implements IExport
{

    /** @var string */
    protected $format = 'Mpdf';

    public function isSupported()
    {
        parent::isSupported();
        if (!class_exists(\Mpdf\Mpdf::class)) {
            throw new BadRequestException('Mpdf not implemented!');
        }
    }

}