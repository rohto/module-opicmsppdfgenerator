<?php

namespace Eadesigndev\PdfGeneratorPro\UI\Component\Sales\Order\Masspdf;

use Eadesigndev\PdfGeneratorPro\Helper\Data;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator\CollectionFactory;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateActive;
use Eadesigndev\PdfGeneratorPro\UI\Component\Sales\Order\Invoice\Masspdf\Pdftemplates as InvoicePdftemplates;
use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

class Pdftemplates implements JsonSerializable
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Additional options params
     *
     * @var array
     */
    private $data;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    private $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    private $paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    private $additionalData = [];

    /**
     * @var Data
     */
    private $helper;

    /**
     * Pdftemplates constructor.
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        Data $helper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->data = $data;
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function jsonSerialize()
    {
        $options = [];

        if (!$this->helper->isEnable()) {
            return InvoicePdftemplates::DISABLED;
        }

        $i = 0;
        if ($this->options === null) {
            // get the massaction data from the database table
            $templateCollection = $this->collectionFactory
                ->create()
                ->addFieldToFilter('template_type', [
                    'eq' => TemplateType::TYPE_ORDER
                ])
                ->addFieldToFilter('is_active', [
                    'eq' => TemplateActive::STATUS_ENABLED
                ]);

            if (empty($templateCollection)) {
                return $this->options;
            }

            foreach ($templateCollection as $key => $badge) {
                $options[$i]['value'] = $badge->getData('template_id');
                $options[$i]['label'] = $badge->getData('template_name');
                $i++;
            }

            $this->prepareData();

            if (empty($options)) {
                return InvoicePdftemplates::DISABLED;
            }

            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type' => 'template_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['value']]
                    );
                }

                $this->options[$optionCode['value']] = array_merge_recursive(
                    $this->options[$optionCode['value']],
                    $this->additionalData
                );
            }

            $this->options = array_values($this->options);
        }

        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    private function prepareData()
    {

        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
