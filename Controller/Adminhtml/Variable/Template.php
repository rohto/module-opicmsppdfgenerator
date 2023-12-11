<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable;

use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\SalesCollect as TaxCustom;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplateRepository;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\DefaultVariables;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Email\Model\BackendTemplateFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;

/**
 * Class Template
 *
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable
 * @SuppressWarnings(CouplingBetweenObjects)
 */
abstract class Template extends Action
{

    const INVOICE_TMEPLTE_ID = 'sales_email_invoice_template';
    const ORDER_TMEPLTE_ID = 'sales_email_order_template';
    const SHIPMENT_TMEPLTE_ID = 'sales_email_shipment_template';
    const CREDITMEMO_TMEPLTE_ID = 'sales_email_creditmemo_template';

    const GUEST_ORDER_TMEPLTE_ID = 'sales_email_order_guest_template';
    const GUEST_INVOICE_TMEPLTE_ID = 'sales_email_invoice_guest_template';
    const GUEST_SHIPMENT_TMEPLTE_ID = 'sales_email_shipment_guest_template';
    const GUEST_CREDITMEMO_TMEPLTE_ID = 'sales_email_creditmemo_guest_template';

    const ADMIN_RESOURCE_VIEW = 'Eadesigndev_PdfGeneratorPro::templates';

    /**
     * @var TemplateRepository
     */
    public $templateRepository;

    /**
     * @var DefaultVariables
     */
    public $defaultVariablesHelper;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var Config
     */
    public $emailConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    public $criteriaBuilder;

    /**
     * @var FilterBuilder
     */
    public $filterBuilder;

    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var BackendTemplateFactory
     */
    public $backendTemplateFactory;

    /**
     * @var mixed
     */
    public $pdfTemplateModel;

    /**
     * @var TaxCustom
     */
    public $taxCustom;

    /**
     * Template constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Config $_emailConfig
     * @param JsonFactory $resultJsonFactory
     * @param DefaultVariables $_defaultVariablesHelper
     * @param SearchCriteriaBuilder $_criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param BackendTemplateFactory $backendTemplateFactory
     * @param TemplateRepository $templateRepository
     * @param TaxCustom $taxCustom
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Config $_emailConfig,
        JsonFactory $resultJsonFactory,
        DefaultVariables $_defaultVariablesHelper,
        SearchCriteriaBuilder $_criteriaBuilder,
        FilterBuilder $filterBuilder,
        BackendTemplateFactory $backendTemplateFactory,
        TemplateRepository $templateRepository,
        TaxCustom $taxCustom
    ) {
        $this->criteriaBuilder         = $_criteriaBuilder;
        $this->filterBuilder           = $filterBuilder;
        $this->emailConfig             = $_emailConfig;
        $this->coreRegistry            = $coreRegistry;
        $this->resultJsonFactory       = $resultJsonFactory;
        $this->defaultVariablesHelper  = $_defaultVariablesHelper;
        $this->backendTemplateFactory  = $backendTemplateFactory;
        $this->templateRepository      = $templateRepository;
        $this->taxCustom               = $taxCustom;
        parent::__construct($context);
    }

    /**
     * Load email template from request
     *
     * @return BackendTemplate $model
     */
    //@codingStandardsIgnoreLine
    protected function _initTemplate()
    {

        $model = $this->backendTemplateFactory->create();

        if (!$this->coreRegistry->registry('email_template')) {
            $this->coreRegistry->register('email_template', $model);
        }

        if (!$this->coreRegistry->registry('current_email_template')) {
            $this->coreRegistry->register('current_email_template', $model);
        }

        return $model;
    }

    /**
     * @param $templateTypeName
     * @return mixed
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
                ->setConditionType('eq')
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
                ->setValue($this->getRequest()->getParam('variables_entity_id'))
                ->setConditionType('eq')
                ->create()]
        );
        $searchCriteria = $this->criteriaBuilder->create();

        //@codingStandardsIgnoreLine
        $collection = $this->_objectManager->create(
            ProductRepositoryInterface::class
        )->getList($searchCriteria);

        if (empty($collection)) {
            return false;
        }

        return $collection->getItems();
    }

    /**
     * @return mixed
     */
    public function addCollection()
    {
        $this->_initTemplate();

        $sourceType = $this->prepareType();
        $collection = $this->collection($sourceType);

        return $collection;
    }

    /**
     * @param $collection
     * @return Item|mixed
     */
    public function dataItem($collection)
    {
        $source = $collection->getLastItem();

        $items = $source->getItems();

        $dataItem = end($items);
        
        return $dataItem;
    }

    /**
     * @param $result
     * @return object $result
     */
    public function addResponse($result)
    {
        if (!empty($result)) {
            return $result;
        }

        $resultJson = $this->resultJsonFactory->create();

        $optionArray[] = ['value' => '{{' . '' . '}}', 'label' => __('%1', '')];

        $optionArray = [
            'label' => __('There are no variable available, please check the source value.'),
            'value' => $optionArray
        ];

        $result = $resultJson->setData(
            [
                $optionArray
            ]
        );

        return $result;
    }

    /**
     * @param $variables
     * @return $this
     */
    public function response($variables)
    {
        $resultJson = $this->resultJsonFactory->create();
        $result = $resultJson->setData($variables);

        return $this->addResponse($result);
    }

    /**
     * @return mixed
     */
    public function prepareType()
    {
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

        return $templateTypeName;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    //@codingStandardsIgnoreLine
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(Templates::ADMIN_RESOURCE_VIEW);
    }
}
