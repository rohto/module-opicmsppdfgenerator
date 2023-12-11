<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea\Edit\Tab;

use Eadesigndev\PdfGeneratorPro\Model\Pdfgenerator;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplatePaperOrientation;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplatePaperForm as TemplatePaperFormat;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Settings
 * @package Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea\Edit\Tab
 */
class Settings extends Generic implements TabInterface
{
    /**
     * @var TemplatePaperOrientation
     */
    private $templatePaperOrientation;

    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * @var TemplatePaperFormat
     */
    private $templatePaperFormat;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * Settings constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TemplatePaperOrientation $templatePaperOrientation
     * @param Yesno $yesNo
     * @param TemplatePaperFormat $templatePaperFormat
     * @param SystemStore $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TemplatePaperOrientation $templatePaperOrientation,
        Yesno $yesNo,
        TemplatePaperFormat $templatePaperFormat,
        SystemStore $systemStore,
        array $data = []
    ) {
        $this->templatePaperFormat = $templatePaperFormat;
        $this->yesNo = $yesNo;
        $this->templatePaperOrientation = $templatePaperOrientation;
        $this->systemStore = $systemStore;
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

        $form->setHtmlIdPrefix('template_');

        $fieldSet = $form->addFieldset('base_fieldset', ['legend' => __('Settings')]);

        if ($model->getId()) {
            $fieldSet->addField('template_id', 'hidden', ['name' => 'template_id']);
        }

        $templateType = $model->getData('template_type');
        if ($templateType != TemplateType::TYPE_SECONDARY_ATTACHMENT) {
            $fieldSet->addField(
                'attachments',
                'text',
                [
                    'name' => 'attachments',
                    'label' => __('Attachments (comma separated)'),
                    'title' => __('Template file name'),
                    'required' => false,
                ]
            );
        }

        $fieldSet->addField(
            'template_file_name',
            'text',
            [
                'name' => 'template_file_name',
                'label' => __('Template file name'),
                'title' => __('Template file name'),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_paper_ori',
            'select',
            [
                'name' => 'template_paper_ori',
                'label' => __('Template paper orientation'),
                'title' => __('Template paper orientation'),
                'values' => $this->templatePaperOrientation->toOptionArray(),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_custom_form',
            'select',
            [
                'name' => 'template_custom_form',
                'label' => __('Custom format'),
                'title' => __('Custom format'),
                'values' => $this->yesNo->toOptionArray(),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_custom_h',
            'text',
            [
                'name' => 'template_custom_h',
                'label' => __('Custom height (mm)'),
                'title' => __('Custom height (mm)'),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_custom_w',
            'text',
            [
                'name' => 'template_custom_w',
                'label' => __('Custom width (mm)'),
                'title' => __('Custom width (mm)'),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_paper_form',
            'select',
            [
                'name' => 'template_paper_form',
                'label' => __('Paper format'),
                'title' => __('Paper format'),
                'values' => $this->templatePaperFormat->toOptionArray(),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_custom_t',
            'text',
            [
                'name' => 'template_custom_t',
                'label' => __('Margin top (mm)'),
                'title' => __('Margin top (mm)'),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_custom_b',
            'text',
            [
                'name' => 'template_custom_b',
                'label' => __('Margin bottom (mm)'),
                'title' => __('Margin bottom (mm)'),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_custom_l',
            'text',
            [
                'name' => 'template_custom_l',
                'label' => __('Margin left (mm)'),
                'title' => __('Margin left (mm)'),
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'template_custom_r',
            'text',
            [
                'name' => 'template_custom_r',
                'label' => __('Margin right (mm)'),
                'title' => __('Margin right (mm)'),
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
        return __('Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Settings');
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
