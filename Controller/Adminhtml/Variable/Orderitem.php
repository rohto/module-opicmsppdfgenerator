<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable;

use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable\Items as ItemsVariable;
use Magento\Framework\Controller\Result\Json;
use Magento\Sales\Model\Order\Item;

class Orderitem extends ItemsVariable
{
    /**
     * @return $this|null
     */
    public function execute()
    {

        $collection = $this->addCollection();
        if (empty($collection)) {
            return null;
        }

        $orderItem = $this->dataItem($collection);

        if (!$orderItem instanceof Item) {
            $orderItem = $orderItem->getOrderItem();
        }

        $lastItem = $this->customData->entity($orderItem)->processAndReadVariables();

        $barCodes = [];

        $templateModel = $this->pdfTemplateModel;

        if (is_object($templateModel)) {
            $barCodesData = $this->pdfTemplateModel->getData('barcode_types');
            if (!empty($barCodesData)) {
                $barCodes = explode(',', $barCodesData);
            }
        }

        $variables = $this->defaultVariablesHelper->getOrderItemsDefault($lastItem, $barCodes);

        /** @var Json $resultJson */
        return $this->response($variables);
    }
}
