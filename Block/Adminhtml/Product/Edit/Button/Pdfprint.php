<?php
namespace Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Model\Product;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Helper\Data;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\Registry;

class Pdfprint extends Generic
{

    private $lastItem = [];

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * Core registry
     *
     * @var Registry
     */
    private $coreRegistry = null;

    /**
     * Pdfprint constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper
    ) {
        $this->coreRegistry = $registry;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $registry);
    }

    /**
     * @return string
     */
    public function getPdfPrintUrl()
    {
        return $this->getUrl(
            'eadesign_pdf/*/printpdf',
            [
                'template_id' => $this->lastItem->getId(),
                'product_id' => $this->registry->registry('current_product')->getId(),
            ]
        );
    }

    public function getButtonData()
    {
        if (!$this->dataHelper->isEnable(Data::ENABLE_PRODUCT)) {
            return [];
        }

        /** @var Product $product */
        $product = $this->registry->registry('current_product');

        if ($product->getStoreId() == null || $product->getStoreId() == 0) {
            $product->setStoreId(1);
        }

        $lastItem = $this->dataHelper->getTemplateStatus(
            $product,
            TemplateType::TYPE_PRODUCT
        );

        if (empty($lastItem->getId())) {
            return null;
        }

        $this->lastItem = $lastItem;

        return [
            'label' => __('Print Pdf'),
            'on_click' => sprintf("location.href = '%s';", $this->getPdfPrintUrl()),
            'sort_order' => 100
        ];
    }
}
