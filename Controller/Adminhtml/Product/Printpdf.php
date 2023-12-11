<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;

class Printpdf extends AbstractPdf
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::catalog_products';

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        $templateId = $this->getRequest()->getParam('template_id');

        $this->templateId = $templateId;

        $this->templateModel();

        $sourceId = $this->getRequest()->getParam('product_id');

        $this->sourceId = $sourceId;

        $this->sourceModel(ProductRepositoryInterface::class);

        $file = $this->returnFile();

        return $file;
    }
}
