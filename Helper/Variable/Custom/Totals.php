<?php

namespace Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom;

use Magento\Catalog\Model\Product;
use Magento\Sales\Block\Order\Creditmemo;
use Magento\Sales\Model\Order;

/**
 * Class SalesCollect
 * @package Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom
 */
class Totals implements CustomInterface
{
    /**
     * @var Order|Order\Invoice|Creditmemo
     */
    private $source;

    /**
     * @param $source
     * @return $this
     */
    public function entity($source)
    {
        if (is_object($source)) {
            $this->source = $source;
            $this->totals();
            return $this;
        }
    }

    /**
     * @return Order|Order\Invoice|Creditmemo
     */
    public function processAndReadVariables()
    {
        return $this->source;
    }

    /**
     * @return $this
     */
    public function totals()
    {
        $source = $this->source;

        if ($source instanceof Product) {
            return $this;
        }

        $order = $this->source->getOrder();

        if ($source instanceof Order) {
            $order = $this->source;
        }

        $total = $order->getData('grand_total');
        $taxAmount = $order->getData('tax_amount');
        $grandTotalExclTax = $total - $taxAmount;

        $this->source->setData('custom_total_grand', $grandTotalExclTax);

        return $grandTotalExclTax;
    }
}
