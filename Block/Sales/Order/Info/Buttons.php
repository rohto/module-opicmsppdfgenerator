<?php
namespace Eadesigndev\PdfGeneratorPro\Block\Sales\Order\Info;

use Eadesigndev\PdfGeneratorPro\Helper\Data;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Info\Buttons as SalesButtons;
use Magento\Sales\Model\Order\Invoice;

class Buttons extends SalesButtons
{
    /**
     * @var Invoice
     */
    private $lastitem;

    /**
     * @var string
     */
    //@codingStandardsIgnoreLine
    protected $_template = 'Eadesigndev_PdfGeneratorPro::Order/Info/buttons.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    //@codingStandardsIgnoreLine
    protected $_coreRegistry = null;

    /**
     * @var HttpContext
     */
    public $httpContext;

    /**
     * Buttons constructor.
     * @param Context $context
     * @param Registry $registry
     * @param HttpContext $httpContext
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        HttpContext $httpContext,
        Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $httpContext,
            $data
        );
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
                $source
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
            'order_id' => $source->getId(),
            'source_id' => $source->getId()
        ]);
    }
}
