<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Shipment;

use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\AbstractPdf;
use Magento\Sales\Api\ShipmentRepositoryInterface;

class Printpdf extends AbstractPdf
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        $templateId = $this->getRequest()->getParam('template_id');

        $this->templateId = $templateId;

        $this->templateModel();

        $sourceId = $this->getRequest()->getParam('shipment_id');

        $this->sourceId = $sourceId;

        $this->sourceModel(ShipmentRepositoryInterface::class);

        $file = $this->returnFile();

        return $file;
    }
}
