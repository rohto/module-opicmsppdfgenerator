<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Invoice;

use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\AbstractPdf;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class Printpdf extends AbstractPdf
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        $templateId = $this->getRequest()->getParam('template_id');

        $this->templateId = $templateId;

        $this->templateModel();

        $sourceId = $this->getRequest()->getParam('invoice_id');

        $this->sourceId = $sourceId;

        $this->sourceModel(InvoiceRepositoryInterface::class);

        $file = $this->returnFile();

        return $file;
    }
}
