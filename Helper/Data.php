<?php

namespace Eadesigndev\PdfGeneratorPro\Helper;

use Eadesigndev\PdfGeneratorPro\Model\Files\Syncrionization;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator\Collection;
use Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator\CollectionFactory as TemplateCollectionFactory;
use Eadesigndev\PdfGeneratorPro\Model\Source\AbstractSource;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateActive;
use Mpdf\Mpdf;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product;

/**
 * Handles the config and other settings
 *
 * Class Data
 * @package Eadesigndev\PdfGeneratorPro\Helper
 */
class Data extends AbstractHelper
{
    const ENABLE_ORDER = 'xtea_pdfgenerator/order/enabled';
    const EMAIL_ORDER = 'xtea_pdfgenerator/order/email';

    const ENABLE_INVOICE = 'xtea_pdfgenerator/invoice/enabled';
    const EMAIL_INVOICE = 'xtea_pdfgenerator/invoice/email';

    const ENABLE_SHIPMENT = 'xtea_pdfgenerator/shipment/enabled';
    const EMAIL_SHIPMENT = 'xtea_pdfgenerator/shipment/email';

    const ENABLE_CREDITMEMO = 'xtea_pdfgenerator/creditmemo/enabled';
    const EMAIL_CREDITMEMO = 'xtea_pdfgenerator/creditmemo/email';

    const ENABLE_PRODUCT = 'xtea_pdfgenerator/product/enabled';

    const ENABLE_SHIPPING = 'xtea_pdfgenerator/shipping/enabled';

    const ENABLE_EDITOR = 'xtea_pdfgenerator/settings/editor';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @var Collection
     */
    private $templateCollection;

    /**
     * @var Module
     */
    private $moduleHelper;

    /**
     * @var Syncronize
     */
    private $synchronization;

    /**
     * Data constructor.
     * @param Context $context
     * @param TemplateCollectionFactory $_templateCollection
     * @param Module $moduleHelper
     * @param Syncrionization $synchronization
     */
    public function __construct(
        Context $context,
        TemplateCollectionFactory $_templateCollection,
        Module $moduleHelper,
        Syncrionization $synchronization
    ) {
        $this->templateCollection = $_templateCollection;
        $this->config             = $context->getScopeConfig();
        $this->moduleHelper       = $moduleHelper;
        $this->synchronization    = $synchronization;
        parent::__construct($context);
    }

    /**
     * @param string $node
     * @return bool|string
     */
    public function isAttachToEmailEnabled($node = self::EMAIL_INVOICE)
    {
        $enableNode = str_replace('email', 'enabled', $node);

        if ($this->isEnable($enableNode)) {
            return $this->getConfig($node);
        }

        return false;
    }

    /**
     * @param string $node
     * @return bool|string
     */
    public function isEnable($node = self::ENABLE_INVOICE)
    {
        if (!$this->moduleHelper->isModuleEnabled()) {
            return false;
        }

        if (!class_exists(Mpdf::class)) {
            return false;
        }

        if (empty($this->collection())) {
            return false;
        }

        return $this->getConfig($node);
    }

    public function isEditorEnabled()
    {
        return $this->getConfig(self::ENABLE_EDITOR);
    }

    /**
     * Get config value
     *
     * @param string $configPath
     * @return string
     */
    public function getConfig($configPath)
    {
        return $this->config->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $source
     * @param int $type
     * @return \Magento\Framework\DataObject
     */
    public function getTemplateStatus($source, $type = TemplateType::TYPE_ORDER)
    {

        if ($source instanceof Order || $source instanceof Product) {
            $store = $source->getStoreId();
        } else {
            $store = $source->getOrder()->getStoreId();
        }

        $collection = $this->collection();
        $collection->addStoreFilter($store);
        $collection->addFieldToFilter(
            'is_active',
            TemplateActive::STATUS_ENABLED
        );
        $collection->addFieldToFilter(
            'template_default',
            AbstractSource::IS_DEFAULT
        );
        $collection->addFieldToFilter(
            'template_type',
            $type
        );

        $lastItem = $collection->getLastItem();

        return $lastItem;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $collection = $this->templateCollection->create();
        return $collection;
    }
}
