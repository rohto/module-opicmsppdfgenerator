<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable;

use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;

/**
 * Class Ajaxload
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable
 */
class Ajaxload extends Template
{

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|null|object
     */
    public function execute()
    {

        $this->_initTemplate();

        $templateType = $this->getRequest()->getParam('template_type');
        if (!$templateType) {
            return null;
        }

        $templateTypeName = TemplateType::TYPES[$templateType];

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $collection = $this->collection($templateTypeName);

        if (empty($collection)) {
            return null;
        }

        $incremetnIdList = [];
        if (is_object($collection)) {
            $data = $collection->getData();
            foreach ($data as $incremetnId) {
                $incremetnIdList[] = $incremetnId['increment_id'];
            }
        } else {
            foreach ($collection as $product) {
                $incremetnIdList[] = $product->getId();
            }
        }

        $result = $resultJson->setData([$incremetnIdList]);

        return $this->addResponse($result);
    }

    /**
     * @param $templateTypeName
     * @return bool|mixed
     */
    public function collection($templateTypeName)
    {
        if ($templateTypeName == 'product') {
            return $this->productCollection();
        }

        $this->criteriaBuilder->addFilters(
            [$this->filterBuilder
                ->setField('increment_id')
                ->setValue($this->getRequest()->getParam('variables_entity_id'))
                ->setConditionType('neq')
                ->create()]
        );
        $searchCriteria = $this->criteriaBuilder->create();

        /** @var AbstractCollection $collection */
        //@codingStandardsIgnoreLine
        $collection = $this->_objectManager->create(
            'Magento\Sales\Api\\' .
            ucfirst($templateTypeName) .
            'RepositoryInterface'
        )->getList($searchCriteria);

        $collection->setPageSize(5);
        $collection->setCurPage(1);

        if (!$collection->getSize()) {
            return false;
        }

        return $collection;
    }

    /**
     * @return []
     */
    public function productCollection()
    {
        $this->criteriaBuilder->addFilters(
            [$this->filterBuilder
                ->setField('entity_id')
                ->setValue(11)
                ->setConditionType('lt')
                ->create()]
        );
        $searchCriteria = $this->criteriaBuilder->create();

        //@codingStandardsIgnoreLine
        $collection = $this->_objectManager->create(
            ProductRepositoryInterface::class
        )->getList($searchCriteria);

        return $collection->getItems();
    }
}
