<?php

namespace Eadesigndev\PdfGeneratorPro\Model\Plugin;

use Eadesigndev\PdfGeneratorPro\Helper\Data;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Registry;

class Printshipment
{

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * Printinvoice constructor.
     * @param Registry $coreRegistry
     * @param UrlInterface $urlInterface
     * @param Data $dataHelper
     */
    public function __construct(
        Registry $coreRegistry,
        UrlInterface $urlInterface,
        Data $dataHelper
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->urlInterface = $urlInterface;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return mixed
     */
    public function getShipment()
    {
        return $this->coreRegistry->registry('current_shipment');
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    //@codingStandardsIgnoreLine
    public function afterGetPrintUrl($subject, $result)
    {
        if (!$this->dataHelper->isEnable(Data::ENABLE_SHIPMENT)) {
            return $result;
        }

        $lastItem = $this->dataHelper->getTemplateStatus(
            $this->getShipment(),
            TemplateType::TYPE_SHIPMENT
        );
        if (empty($lastItem->getId())) {
            return $result;
        }

        return $this->_print($lastItem);
    }

    /**
     * @param $lastItem
     * @return string
     */
    private function _print($lastItem)
    {
        return $this->urlInterface->getUrl(
            'eadesign_pdf/*/printpdf',
            [
                'template_id' => $lastItem->getId(),
                'order_id' => $this->getShipment()->getOrder()->getId(),
                'shipment_id' => $this->getShipment()->getId()
            ]
        );
    }
}
