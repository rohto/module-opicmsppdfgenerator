<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Sales\Order;

use Eadesigndev\PdfGeneratorPro\Helper\Data;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class PrintPDF extends Container
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
     * PrintPDF constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {

        if (!$this->dataHelper->isEnable(Data::ENABLE_ORDER)) {
            return $this;
        }

        $lastItem = $this->dataHelper->getTemplateStatus(
            $this->coreRegistry->registry('sales_order'),
            TemplateType::TYPE_ORDER
        );

        if (empty($lastItem->getId())) {
            return null;
        }
        $this->lastItem = $lastItem;

        $this->addButton(
            'xtea_print',
            [
                'label' => 'Print',
                'class' => 'print',
                'onclick' => 'setLocation(\'' . $this->getPdfPrintUrl() . '\')'
            ]
        );

        parent::_construct();
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
                'order_id' => $this->getOrderId(),
            ]
        );
    }

    /**
     * @return integer
     */
    public function getOrderId()
    {
        return $this->coreRegistry->registry('sales_order')->getId();
    }
}
