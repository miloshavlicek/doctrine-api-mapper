<?php

namespace Miloshavlicek\DoctrineApiMapper\Export;

abstract class AExport
{

    /** @var array */
    protected $data = [];

    protected $extension = '.txt';

    protected $contentType = 'text/plain';

    protected function setHeaders()
    {
        header('Content-Type: ' . $this->contentType);
        header('Content-Disposition: attachement; filename="' . $this->getFileName() . '";');
    }

    private function getFileName()
    {
        return 'export_' . date('Y-n-j_H-i-s') . $this->extension;
    }

}