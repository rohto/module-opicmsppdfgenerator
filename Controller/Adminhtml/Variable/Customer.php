<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable;

use Eadesigndev\PdfGeneratorPro\Helper\Variable\DefaultVariables;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplateRepository;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\SalesCollect as TaxCustom;
use Magento\Backend\App\Action\Context;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject\Factory as DataObject;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Email\Model\BackendTemplateFactory;

/**
 * Class Customer
 *
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Customer extends Template
{

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * Customer constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Config $_emailConfig
     * @param JsonFactory $resultJsonFactory
     * @param TemplateRepository $templateRepository
     * @param DefaultVariables $_defaultVariablesHelper
     * @param SearchCriteriaBuilder $_criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param DataObject $dataObject
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param BackendTemplateFactory $backendTemplateFactory
     * @SuppressWarnings(ExcessiveParameterList)
     */
    //@codingStandardsIgnoreLine
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Config $_emailConfig,
        JsonFactory $resultJsonFactory,
        TemplateRepository $templateRepository,
        DefaultVariables $_defaultVariablesHelper,
        SearchCriteriaBuilder $_criteriaBuilder,
        FilterBuilder $filterBuilder,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        DataObject $dataObject,
        CustomerRepositoryInterface $customerRepositoryInterface,
        BackendTemplateFactory $backendTemplateFactory,
        TaxCustom $taxCustom
    ) {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->templateRepository            = $templateRepository;
        $this->coreRegistry                  = $coreRegistry;
        $this->dataObject                    = $dataObject;
        $this->customerRepositoryInterface   = $customerRepositoryInterface;
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
            $this->taxCustom = $taxCustom
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
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

        if ($customerId = $order->getCustomerId()) {
            $customer = $this->customerRepositoryInterface
                ->getById($customerId);

            $customerData = $this->extensibleDataObjectConverter->toFlatArray(
                $customer,
                [],
                '\Magento\Customer\Api\Data\CustomerInterface'
            );
        }

        $barCodes = [];

        $templateModel = $this->pdfTemplateModel;

        if (is_object($templateModel)) {
            $barCodesData = $this->pdfTemplateModel->getData('barcode_types');
            if (!empty($barCodesData)) {
                $barCodes = explode(',', $barCodesData);
            }
        }

        $pseudoCustomer = $this->dataObject->create($customerData);

        $invoiceVariables = $this->defaultVariablesHelper->getCustomerDefault($pseudoCustomer, $barCodes);

        $result = $resultJson->setData($invoiceVariables);

        return $this->addResponse($result);
    }
}
