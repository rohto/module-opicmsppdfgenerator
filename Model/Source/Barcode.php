<?php

namespace Eadesigndev\PdfGeneratorPro\Model\Source;

use Eadesigndev\PdfGeneratorPro\Helper\AbstractPDF;

class Barcode extends AbstractSource
{
    /**
     * @return array, options for the code bar system
     */
    public function getAvailable()
    {
        foreach (AbstractPDF::CODE_BAR as $code) {
            $options[$code] = $code;
        }

        return $options;
    }
}
