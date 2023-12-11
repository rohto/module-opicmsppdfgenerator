<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable;

use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\Items as VariableItems;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\Product as ProductHelperData;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\DefaultVariables;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplateRepository;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\SalesCollect as TaxCustom;
use Magento\Backend\App\Action\Context;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Email\Model\BackendTemplateFactory;
use Magento\Sales\Model\Order\Item;

class Product extends Template
{

    /**
     * @var VariableItems
     */
    public $customData;

    /**
     * Items constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Config $_emailConfig
     * @param JsonFactory $resultJsonFactory
     * @param TemplateRepository $templateRepository
     * @param DefaultVariables $_defaultVariablesHelper
     * @param SearchCriteriaBuilder $_criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ProductHelperData $customData
     * @param BackendTemplateFactory $backendTemplateFactory
     * @SuppressWarnings(ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Config $_emailConfig,
        JsonFactory $resultJsonFactory,
        TemplateRepository $templateRepository,
        DefaultVariables $_defaultVariablesHelper,
        SearchCriteriaBuilder $_criteriaBuilder,
        FilterBuilder $filterBuilder,
        ProductHelperData $customData,
        BackendTemplateFactory $backendTemplateFactory,
        TaxCustom $taxCustom
    ) {
        $this->templateRepository = $templateRepository;
        parent::__construct(
            $context,
            $coreRegistry,
            $_emailConfig,
            $resultJsonFactory,
            $_defaultVariablesHelper,
            $_criteriaBuilder,
            $filterBuilder,
            $backendTemplateFactory,
            $templateRepository,
            $taxCustom
        );
        $this->coreRegistry = $coreRegistry;
        $this->customData = $customData;
    }

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

        $orderItemProduct = $orderItem->getProduct();
        $lastItem = $this->customData->entity($orderItemProduct)->processAndReadVariables();

        $barCodes = [];

        $templateModel = $this->pdfTemplateModel;

        if (is_object($templateModel)) {
            $barCodesData = $this->pdfTemplateModel->getData('barcode_types');
            if (!empty($barCodesData)) {
                $barCodes = explode(',', $barCodesData);
            }
        }

        $variables = $this->defaultVariablesHelper->getOrderItemsProductDefault($lastItem, $barCodes);

        /** @var Json $resultJson */
        return $this->response($variables);
    }
}
