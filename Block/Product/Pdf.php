<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Product;

use Eadesigndev\PdfGeneratorPro\Helper\Data;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Pdf extends Template
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    private $lastitem;

    /**
     * Pdf constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
    }

    /**
     * @return bool
     */
    public function addPDFLink()
    {
        $product = $this->product();

        $helper = $this->helper;

        if ($helper->isEnable()) {
            $lastItem = $helper->getTemplateStatus(
                $product,
                TemplateType::TYPE_PRODUCT
            );

            if (!empty($lastItem->getId())) {
                $this->lastitem = $lastItem;
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getPrintPDFUrl()
    {

        $product = $this->product();

        return $this->getUrl('eadesign_pdf/index/product', [
            'template_id' => $this->lastitem->getId(),
            'product_id' => $product->getId()
        ]);
    }

    private function product()
    {
        $product = $this->registry->registry('current_product');
        $storeId = $this->storeManager->getStore()->getId();
        $product->setStoreId($storeId);

        return $product;
    }
}
