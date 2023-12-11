<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Sales\Shipment;

use Eadesigndev\PdfGeneratorPro\Helper\Data;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

class Items extends \Magento\Shipping\Block\Items
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    private $lastitem;

    /**
     * Items constructor.
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
        parent::__construct($context, $registry, $data);
        $this->helper = $helper;
    }

    /**
     * @param $source
     * @return bool
     */
    public function addPDFLink($source)
    {
        $helper = $this->helper;

        if ($helper->isEnable()) {
            $lastItem = $helper->getTemplateStatus(
                $source,
                TemplateType::TYPE_SHIPMENT
            );

            if (!empty($lastItem->getId())) {
                $this->lastitem = $lastItem;
                return true;
            }
        }

        return false;
    }

    /**
     * @param $source
     * @return string
     */
    public function getPrintPDFUrl($source)
    {
        return $this->getUrl('eadesign_pdf/index/index', [
            'template_id' => $this->lastitem->getId(),
            'order_id' => $source->getOrder()->getId(),
            'source_id' => $source->getId()
        ]);
    }
}
