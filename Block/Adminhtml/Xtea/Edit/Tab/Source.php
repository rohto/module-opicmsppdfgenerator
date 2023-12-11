<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea\Edit\Tab;

use Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea\Edit\Renderer\Editor;
use Eadesigndev\PdfGeneratorPro\Model\Pdfgenerator;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Model\UrlInterface as ButtonsVariable;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;

/**
 * Class Source
 * @package Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea\Edit\Tab
 */
class Source extends Generic implements TabInterface
{

    /**
     * @var WysiwygConfig
     */
    private $wysiwygConfig;

    /**
     * @var ButtonsVariable
     */
    private $buttonsVariable;

    /**
     * Body constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param WysiwygConfig $wysiwygConfig
     * @param ButtonsVariable $buttonsVariable
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        WysiwygConfig $wysiwygConfig,
        ButtonsVariable $buttonsVariable,
        array $data = []
    ) {
        $this->wysiwygConfig   = $wysiwygConfig;
        $this->buttonsVariable = $buttonsVariable;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     */
    public function _prepareForm()
    {

        /** @var Pdfgenerator $model */
        $model = $this->_coreRegistry->registry('pdfgenerator_template');

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('source_');

        $fieldSet = $form->addFieldset('base_fieldset', ['legend' => __('Settings')]);

        if ($model->getId()) {
            $fieldSet->addField('template_id', 'hidden', ['name' => 'template_id']);
        }

        $disableSource = false;
        $required = true;
        if ($model->getData('template_type') == TemplateType::TYPE_SECONDARY_ATTACHMENT) {
            $disableSource = 'disabled';
            $required = false;
        }

        $url = $this->getUrl(
            'eadesign_pdf/variable/ajaxload',
            ['template_type' => $model->getData('template_type')]
        );

        $model->setData('ajax_search', $url);
        $model->setData('type_id', $model->getData('template_type'));

        $fieldSet->addField(
            'type_id',
            'hidden',
            ['name' => 'type_id']
        );

        $fieldSet->addField(
            'ajax_search',
            'hidden',
            ['name' => 'ajax_search']
        );

        $fieldSet->addField(
            'source',
            'text',
            [
                'name' => 'source',
                'label' => __('Source id for variables'),
                'title' => __('Source id for variables'),
                'required' => $required,
                'disabled' => $disableSource,
                'after_element_html' => __(
                    'Here you need to add the increment id of the source for you want to use the template for.
                    This will be used for all the variables from the body and footer.'
                ),
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
        return __('Template source');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Template source');
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
