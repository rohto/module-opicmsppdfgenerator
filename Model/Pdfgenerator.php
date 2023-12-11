<?php

namespace Eadesigndev\PdfGeneratorPro\Model;

use Eadesigndev\PdfGeneratorPro\Api\Data\TemplatesInterface;
use Magento\Framework\Model\AbstractModel;

class Pdfgenerator extends AbstractModel implements TemplatesInterface
{

    /**
     * Init resource model for the templates
     * @return void
     */
    public function _construct()
    {
        $this->_init('Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator');
    }
}
