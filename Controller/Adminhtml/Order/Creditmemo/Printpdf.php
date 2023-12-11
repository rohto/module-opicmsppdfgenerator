<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Creditmemo;

use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\AbstractPdf;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

class Printpdf extends AbstractPdf
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        $templateId = $this->getRequest()->getParam('template_id');

        $this->templateId = $templateId;

        $this->templateModel();

        $sourceId = $this->getRequest()->getParam('creditmemo_id');

        $this->sourceId = $sourceId;

        $this->sourceModel(CreditmemoRepositoryInterface::class);

        $file = $this->returnFile();

        return $file;
    }
}
