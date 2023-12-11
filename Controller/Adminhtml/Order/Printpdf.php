<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order;

use Magento\Sales\Api\OrderRepositoryInterface;

class Printpdf extends AbstractPdf
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        $templateId = $this->getRequest()->getParam('template_id');

        $this->templateId = $templateId;

        $this->templateModel();

        $sourceId = $this->getRequest()->getParam('order_id');

        $this->sourceId = $sourceId;

        $this->sourceModel(OrderRepositoryInterface::class);

        $file = $this->returnFile();

        return $file;
    }
}
