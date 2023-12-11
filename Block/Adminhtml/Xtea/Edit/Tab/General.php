<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea\Edit\Tab;

use Eadesigndev\PdfGeneratorPro\Model\Pdfgenerator;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Model\Source\Barcode as BarcodeTypes;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;

/**
 * Class Main
 * @package Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var TemplateType
     */
    private $templateType;

    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * @var BarcodeTypes
     */
    private $barcodeTypes;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * General constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TemplateType $templateType
     * @param Yesno $yesNo
     * @param BarcodeTypes $barcodeTypes
     * @param SystemStore $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TemplateType $templateType,
        Yesno $yesNo,
        BarcodeTypes $barcodeTypes,
        SystemStore $systemStore,
        array $data = []
    ) {
        $this->templateType = $templateType;
        $this->yesNo = $yesNo;
        $this->barcodeTypes = $barcodeTypes;
        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareForm()
    {

        /** @var Pdfgenerator $model */
        $model = $this->_coreRegistry->registry('pdfgenerator_template');

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('template_');

        $fieldSet = $form->addFieldset('base_fieldset', ['legend' => __('Settings')]);

        if ($model->getId()) {
            $fieldSet->addField('template_id', 'hidden', ['name' => 'template_id']);
        }

        $fieldSet->addField(
            'template_name',
            'text',
            [
                'name' => 'template_name',
                'label' => __('Template name'),
                'title' => __('Template name'),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' => __('Enable template'),
                'title' => __('Enable template'),
                'values' => $this->yesNo->toOptionArray(),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_default',
            'select',
            [
                'name' => 'template_default',
                'label' => __('Default template'),
                'title' => __('Default template'),
                'values' => $this->yesNo->toOptionArray(),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'barcode_types',
            'multiselect',
            [
                'name' => 'barcode_types',
                'label' => __('Use barcode types'),
                'title' => __('Use barcode types'),
                'values' => $this->barcodeTypes->toOptionArray(),
                'required' => false,
                'after_element_html' => __(
                    'In order for the variables to be loaded you need to save the template first.
                    Then the variable buttons will be updated'
                ),
            ]
        );

        $types = $this->templateType->toOptionArray();

        if ($type = $model->getData('template_type')) {
            $onlyType[] = $types[$type];
        }

        $fieldSet->addField(
            'template_type',
            'select',
            [
                'name' => 'template_type',
                'label' => __('Template type'),
                'title' => __('Template type'),
                'values' => $onlyType,
                'required' => true,
                'readonly' => true,
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldSet->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'store_id',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldSet->addField(
                'stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldSet->addField(
            'template_description',
            'text',
            [
                'name' => 'template_description',
                'label' => __('Template description'),
                'title' => __('Template description'),
                'required' => true,
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        parent::_prepareForm();

        return $this;
    }

    /**
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
