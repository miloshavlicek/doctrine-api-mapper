<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

use Miloshavlicek\DoctrineApiMapper\Exception\BadRequestException;
use Miloshavlicek\DoctrineApiMapper\Export\PDF\Dompdf;
use Miloshavlicek\DoctrineApiMapper\Export\PDF\Mpdf;
use Miloshavlicek\DoctrineApiMapper\Export\PDF\Tcpdf;

class Pdf implements IExport
{

    private $processors = [
        Dompdf::class,
        Tcpdf::class,
        Mpdf::class
    ];

    public function __construct(array $data, ?array $processors = null)
    {
        if ($processors !== null) {
            $this->processors = $processors;
        }
        $this->isSupported();
        $this->data = $data;
    }

    public function isSupported()
    {
        $found = [];
        foreach ($this->processors as $processor) {
            try {
                if (!class_exists($processor)) {
                    continue;
                }

                (new $processor([]))->isSupported();

                // Filter one for use
                $found[] = $processor;
                break;
            } catch (BadRequestException $e) {
            }
        }
        if (count($found) === 0) {
            throw new BadRequestException('No PDF processor found!');
        }
        $this->processors = $found;
    }

    public function generateFile()
    {
        $this->getProcessor()->generateFile();
    }

    private function getProcessor(): IExport
    {
        if (isset($this->processors[0]) && class_exists($this->processors[0])) {
            return new $this->processors[0]($this->data);
        }

        throw new BadRequestException('No PDF processor found!');
    }

}