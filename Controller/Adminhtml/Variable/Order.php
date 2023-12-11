<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable;

use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Framework\Controller\Result\Json;
use Magento\Sales\Model\Order as SalesOrder;

class Order extends Template
{

    /**
     * @return $this|null
     */
    public function execute()
    {

        $this->_initTemplate();

        $id = $this->getRequest()->getParam('template_id');
        $type = $this->getRequest()->getPostValue('type_id');

        if ($type) {
            $templateTypeName = TemplateType::TYPES[$type];
        }

        if ($id) {
            $templateModel = $this->templateRepository->getById($id);
            $templateType = $templateModel->getData('template_type');

            $templateTypeName = TemplateType::TYPES[$templateType];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $collection = $this->collection($templateTypeName);

        if (empty($collection)) {
            return null;
        }

        $source = $collection->getLastItem();

        if ($source instanceof SalesOrder) {
            $order = $source;
        } else {
            $order = $source->getOrder();
        }

        $model = $this->templateRepository->getById($id);
        $barCodes = [];
        if (!empty($model->getData('barcode_types'))) {
            $barCodes = explode(',', $model->getData('barcode_types'));
        }

        $invoiceVariables = $this->defaultVariablesHelper->getOrderDefault($order, $barCodes);

        $result = $resultJson->setData($invoiceVariables);

        return $this->addResponse($result);
    }
}
