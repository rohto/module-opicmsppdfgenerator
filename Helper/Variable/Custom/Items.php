<?php

namespace Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom;

use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\AbstractCustomHelper;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item;

class Items implements CustomInterface
{

    /**
     * @var Object
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
            return $this;
        }

        $this->addTaxPercent();
    }

    /**
     * @return Object
     */
    public function processAndReadVariables()
    {
        $this->addTaxPercent();
        $this->addItemOptions();
        return $this->source;
    }

    /**
     * @return Item|Object
     */
    private function addTaxPercent()
    {
        if (!$this->source instanceof Item) {
            $orderItem = $this->source->getOrderItem();
        } else {
            $orderItem = $this->source;
        }

        $taxPercent = number_format($orderItem->getTaxPercent(), 2);

        $this->source->setData(
            OrderItemInterface::TAX_PERCENT,
            $taxPercent
        );

        return $this->source;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function addItemOptions()
    {
        if (!$this->source instanceof Item) {
            $orderItem = $this->source->getOrderItem();
        } else {
            $orderItem = $this->source;
        }

        $result = [];
        if ($options = $orderItem->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        $data = '';

        if (!empty($result)) {
            foreach ($result as $option => $value) {
                $data .= $value['label'] . ' - ' . $value['value'] . '<br>';
            }

            $this->source->setData(
                'item_options',
                $data
            );
        }

        $this->source->setData(
            'item_options',
            $data
        );
    }
}
