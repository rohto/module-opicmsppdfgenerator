<?php


namespace Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom;

interface CustomInterface
{
    /**
     * @return object
     */
    public function processAndReadVariables();

    /**
     * @param $source
     * @return object
     */
    public function entity($source);
}
