<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable;

use Eadesigndev\PdfGeneratorPro\Helper\Variable\DefaultVariables;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\SalesCollect as TaxCustom;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Email\Model\Source\Variables;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplateRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\Json\Helper\Data as JsonData;
use Magento\Variable\Model\Variable;
use Magento\Variable\Model\VariableFactory as VariableModelFactory;
use Eadesigndev\PdfGeneratorPro\Model\Email\VariablesLoaderFactory;
use Magento\Email\Model\BackendTemplateFactory;

/**
 * Class Standard
 *
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Variable
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Standard extends Template
{

    /**
     * @var JsonData
     */
    private $jsonData;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Variable
     */
    private $variableModelFactory;

    /**
     * @var Variables
     */
    private $variablesModelSourceFactory;

    /**
     * Currency constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Config $_emailConfig
     * @param JsonFactory $resultJsonFactory
     * @param TemplateRepository $templateRepository
     * @param DefaultVariables $_defaultVariablesHelper
     * @param SearchCriteriaBuilder $_criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param JsonData $jsonData
     * @param VariableModelFactory $variableModelFactory
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
        JsonData $jsonData,
        VariableModelFactory $variableModelFactory,
        VariablesLoaderFactory $variablesModelSourceFactory,
        BackendTemplateFactory $backendTemplateFactory,
        TaxCustom $taxCustom
    ) {
        $this->context = $context;
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
        $this->jsonData = $jsonData;
        $this->variableModelFactory = $variableModelFactory;
        $this->variablesModelSourceFactory = $variablesModelSourceFactory;
    }

    /**
     * @return $this|null
     */
    public function execute()
    {

        $template = $this->_initTemplate();

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

        /**if json error see https://github.com/magento/magento2/commit/02bc3fc42bf041919af6200f5dbba071ae3f2020 */

        try {
            $parts = $this->emailConfig->parseTemplateIdParts('sales_email_' . $templateTypeName . '_template');
            $templateId = $parts['templateId'];
            $theme = $parts['theme'];

            if ($theme) {
                $template->setForcedTheme($templateId, $theme);
            }
            $template->setForcedArea($templateId);

            $template->loadDefault($templateId);
            $template->setData('orig_template_code', $templateId);
            $template->setData('template_variables', \Zend_Json::encode($template->getVariablesOptionArray(true)));

            //$templateBlock = $this->_view->getLayout()->createBlock('Magento\Email\Block\Adminhtml\Template\Edit');
            //$template->setData('orig_template_currently_used_for', $templateBlock->getCurrentlyUsedForPaths(false));

            $this->getResponse()->representJson(
                $this->jsonData->jsonEncode($template->getData())
            );
        } catch (Exception $e) {
            $this->context->getMessageManager()->addExceptionMessage($e, $e->getMessage());
        }

        $customVariables = $this->variableModelFactory->create()
            ->getVariablesOptionArray(true);

        $storeContactVariables = $this->variablesModelSourceFactory->create()
            ->toOptionArray(true);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $result = $resultJson->setData(
            [
                $storeContactVariables,
                $template->getVariablesOptionArray(true),
                $customVariables
            ]
        );

        return $this->addResponse($result);
    }
}
