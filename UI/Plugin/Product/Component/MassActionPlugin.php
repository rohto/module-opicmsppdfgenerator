<?php

namespace Eadesigndev\PdfGeneratorPro\UI\Plugin\Product\Component;

use Eadesigndev\PdfGeneratorPro\Helper\Data;
use Eadesigndev\PdfGeneratorPro\Model\Pdfgenerator;
use Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator\CollectionFactory;
use Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator\Collection as PdfCollection;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateActive;
use Magento\Backend\Helper\Data as AdminhtmlData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Ui\Component\MassAction;

/**
 * Class MassActionPlugin
 * @package Eadesigndev\PdfGeneratorPro\UI\Plugin\Product\Component
 */
class MassActionPlugin
{
    /**
     * @var Data
     */
    private $moduleHelper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Adminhtml data
     *
     * @var AdminhtmlData
     */
    private $adminhtmlData = null;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * MassActionPlugin constructor.
     * @param Data $moduleHelper
     * @param RequestInterface $request
     * @param ScopeConfigInterface $config
     * @param Registry $registry
     * @param AuthorizationInterface $authorization
     * @param AdminhtmlData $adminhtmlData
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Data $moduleHelper,
        RequestInterface $request,
        ScopeConfigInterface $config,
        Registry $registry,
        AuthorizationInterface $authorization,
        AdminhtmlData $adminhtmlData,
        CollectionFactory $collectionFactory
    ) {
        $this->moduleHelper      = $moduleHelper;
        $this->request           = $request;
        $this->scopeConfig       = $config;
        $this->registry          = $registry;
        $this->authorization     = $authorization;
        $this->adminhtmlData     = $adminhtmlData;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Add massactions to the Products > Catalog grid.
     * Why not via XML? Because then you cannot select the actions which should be shown from
     * the Magento admin, this is required so admins can adjust the actions via the configuration.
     *
     * @param MassAction $subject
     * @param string $interceptedOutput
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // @codingStandardsIgnoreStart
    public function afterPrepare(MassAction $subject, $interceptedOutput)
    {
        if (!$this->moduleHelper->isEnable()) {
            return;
        }

        if (!$this->authorization->isAllowed('Eadesigndev_PdfGeneratorPro::templates')) {
            return;
        }

        $dataProvider = $subject->getContext()->getDataProvider()->getName();
        preg_match('/(.*)\_listing_data/', $dataProvider, $dataProviderMatches);

        if (!isset($dataProviderMatches[1]) && empty($dataProviderMatches[1])) {
            return;
        }

        $config = $subject->getData('config');

        if (!isset($config['component']) || strstr($config['component'], 'tree') === false) {
            // Temporary until added to core to support multi-level selects
            $config['component'] = 'Magento_Ui/js/grid/tree-massactions';
        }

        /** @var PdfCollection $templateCollection */
        $templateCollection = $this->collectionFactory
            ->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('template_type', [
                'eq' => TemplateType::TYPE_PRODUCT
            ])
            ->addFieldToFilter('is_active', [
                'eq' => TemplateActive::STATUS_ENABLED
            ])
            ->getItems();

        if (empty($templateCollection)) {
            return;
        }

        if (!isset($config['actions'])) {
            return;
        }

        $config['actions'] = $this->addExportAction($config['actions'], $templateCollection);

        $subject->setData('config', $config);
    }

    /**
     * @param $configActions
     * @param PdfCollection $templateCollection
     * @return array
     */
    private function addExportAction($configActions, $templateCollection)
    {
        $subActions = [];
        /** @var Pdfgenerator $item */
        foreach ($templateCollection as $item) {
            $subActions[] = [
                'type' => 'pdf_' . $item->getData('template_id'),
                'label' => $item['template_name'],
                'url' => $this->adminhtmlData->getUrl(
                    'eadesign_pdf/product_massaction/printpdf/',
                    [
                        'template_id' => $item->getData('template_id'),
                    ]
                )
            ];
        }

        $configActions[] = [
            'type' => 'xtea_product_export',
            'label' => __('PDF Catalog'),
            'actions' => $subActions
        ];

        return $configActions;
    }
}
