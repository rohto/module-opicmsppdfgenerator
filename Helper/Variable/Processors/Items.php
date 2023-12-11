<?php

namespace Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors;

use Eadesigndev\PdfGeneratorPro\Helper\AbstractPDF;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\Items as CustomItems;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\Product as CustomProduct;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Formated;
use Eadesigndev\PdfGeneratorPro\Model\Template\Processor;
use Eadesigndev\PdfGeneratorPro\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject\Factory as DataObject;
use Magento\Sales\Model\Order\Item;

/**
 * Class Items
 * @package Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Items extends AbstractHelper
{
    /**
     * @var Formated
     */
    private $formated;

    /**
     * @var CustomItems
     */
    private $customData;

    /**
     * @var Processor
     */
    public $processor;

    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var CustomProduct
     */
    private $customProduct;

    private $dataHelper;

    /**
     * Items constructor.
     * @param Context $context
     * @param Processor $processor
     * @param Formated $formated
     * @param CustomItems $customData
     * @param DataObject $dataObject
     * @param CustomProduct $customProduct
     */
    public function __construct(
        Context $context,
        Processor $processor,
        Formated $formated,
        CustomItems $customData,
        DataObject $dataObject,
        CustomProduct $customProduct,
        Data $dataHelper
    ) {
        $this->formated = $formated;
        $this->customData = $customData;
        $this->processor = $processor;
        $this->dataObject = $dataObject;
        $this->customProduct = $customProduct;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    /**
     * @param $standardItem
     * @param $template
     * @return string
     */
    public function variableItemProcessor($standardItem, $template)
    {

        $item = $this->customData->entity($standardItem)->processAndReadVariables();

        /** @var Item $orderItem */
        $orderItem = $this->orderItem($item);
        $orderItemProduct = $orderItem->getProduct();

        $orderItemProduct = $this->customProduct->entity($orderItemProduct)->processAndReadVariables();

        $transport = [
            'item' => $item,
            'custom_item' => $this->formated->getFormated($item),
            'custom_item_if' => $this->formated->getZeroFormated($item),
            'order.item' => $orderItem,
            'order.custom_item' => $this->formated->getFormated($orderItem),
            'order.custom_item_if' => $this->formated->getZeroFormated($orderItem),
            'order_item_product' => $orderItemProduct,
            'order_custom_item_product' => $this->formated->getFormated($orderItemProduct),
            'order_custom_item_product_if' => $this->formated->getZeroFormated($orderItemProduct),
        ];

        foreach (AbstractPDF::CODE_BAR as $code) {
            $transport['custom_barcode_' . $code . '_item'] = $this->formated->getBarcodeFormated(
                $item,
                $code
            );
            $transport['custom_barcode_' . $code . '_order.item'] = $this->formated->getBarcodeFormated(
                $orderItem,
                $code
            );
            $transport['custom_barcode_' . $code . '_order_item_product'] = $this->formated->getBarcodeFormated(
                $orderItem,
                $code
            );
        }

        $processor = $this->processor;

        $processor->setVariables($transport);
        $processor->setTemplate($template);

        $parts = $processor->processTemplate();

        return $parts;
    }

    public function getShippingLine($shippingItem, $source)
    {
        $vat = ($shippingItem->getData('tax_percent')) ? ((100+$shippingItem->getData('tax_percent'))/100) :1;
        $shippingItem->setData('name', 'Transport');
        $shippingItem->setData('sku', '');
        $shippingItem->setData('price_incl_tax', $source->getShippingAmount());
        $shippingItem->setData('price', $source->getShippingAmount() / $vat);
        $shippingItem->setData('tax_amount', $source->getShippingAmount() - ($source->getShippingAmount() / $vat));
        $shippingItem->setData('tax_percent', $shippingItem->getData('tax_percent'));
        $shippingItem->setData('row_total', $source->getShippingAmount() / $vat);
        $shippingItem->setData('row_total_incl_tax', $source->getShippingAmount());
        return $shippingItem;
    }

    /**
     * @param $source
     * @param $templateModel
     * @return string
     */
    public function processItems($source, $templateModel)
    {
        $items = $source->getItems();
        if ($this->dataHelper->isEnable(Data::ENABLE_SHIPPING)) {
            $items[] = $this->getShippingLine(clone end($items), $source);
        }

        $this->formated->applySourceOrder($source);
        $templateBodyParts = $this->formated->getItemsArea(
            $templateModel->getData('template_body'),
            '##productlist_start##',
            '##productlist_end##'
        );
        $itemHtml = '';

        $i = 1;
        foreach ($items as $item) {
            $item->setData('position', $i++);

            if ($item instanceof Item) {
                if ($parentItem = $item->getParentItem()) {
                    if ($parentItem->getData('product_type') != Type::TYPE_BUNDLE) {
                        continue;
                    } else {
                        $item->setData('position', '');
                    }
                }
            } else {
                if ($parentItem = $item->getOrderItem()->getParentItem()) {
                    if ($parentItem->getData('product_type')  != Type::TYPE_BUNDLE) {
                        continue;
                    }
                    $item->setData('position', '');
                }
            }
            $itemBodyParts = $this->dataObject->create(['template_body' => $templateBodyParts[1]]);
            $processedItem = $this->variableItemProcessor($item, $itemBodyParts);
            $itemHtml .= $processedItem['body'];
        }

        $template = $templateBodyParts[0] . $itemHtml . $templateBodyParts[2];
        return $template;
    }

    /**
     * @param $item
     * @return mixed
     */
    private function orderItem($item)
    {
        if (!$item instanceof Item) {
            $orderItem = $item->getOrderItem();
            $item = $this->customData->entity($orderItem)->processAndReadVariables();
            return $item;
        }

        return $item;
    }
}
