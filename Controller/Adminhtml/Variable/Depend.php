<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable;

use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;

class Depend extends Template
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

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $collection = $this->collection($templateTypeName);

        if (empty($collection)) {
            return null;
        }

        if (is_object($collection)) {
            $source = $collection->getLastItem();
        } else {
            $source = end($collection);
        }

        $source = $this->taxCustom->entity($source)->processAndReadVariables();

        $invoiceVariables = $this->defaultVariablesHelper
            ->setSourceType($source, $templateTypeName)
            ->getDependDefault();

        $result = $resultJson->setData([$invoiceVariables]);

        return $this->addResponse($result);
    }
}
