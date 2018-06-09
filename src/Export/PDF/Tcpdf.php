<?php

namespace Miloshavlicek\DoctrineApiMapper\Export\PDF;

use Miloshavlicek\DoctrineApiMapper\Exception\BadRequestException;
use Miloshavlicek\DoctrineApiMapper\Export\IExport;

class Tcpdf extends APdf implements IExport
{

    /** @var string */
    protected $format = 'Tcpdf';

    public function isSupported()
    {
        parent::isSupported();
        if (!class_exists(\TCPDF::class)) {
            throw new BadRequestException('Tcpdf not implemented!');
        }
    }

}