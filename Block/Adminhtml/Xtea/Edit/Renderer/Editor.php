<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea\Edit\Renderer;

use Eadesigndev\PdfGeneratorPro\Model\Pdfgenerator;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Registry;
use Magento\Backend\Model\UrlInterface;
use Magento\Backend\Block\Widget\Button;

/**
 * Class Editor
 * @package Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea\Edit\Renderer
 */
class Editor extends Element implements RendererInterface
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Pdfgenerator
     */
    private $pdfModel;

    /**
     * Editor constructor.
     * @param Context $context
     * @param Registry $registry
     * @param UrlInterface $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UrlInterface $url,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->url = $url;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        $html = $this->toHtml();
        $htmlWithButtons = $this->addButtonHtml().$html;
        return $htmlWithButtons;
    }

    /**
     * @return string
     */
    private function addButtonHtml()
    {
        $html = '';
        if ($this->_element->getData('wysiwyg')) {

            /** @var Pdfgenerator $model */
            $model = $this->registry->registry('pdfgenerator_template');
            $this->pdfModel = $model;

            $barcode = '';
            if ($model->getData('barcode_types')) {
                $barcode = $this->getVariableBarcodesButtonHtml();
            }

            if ($model->getData('template_type') == TemplateType::TYPE_SECONDARY_ATTACHMENT) {
                return $html;
            }

            if ($model->getData('template_type') == TemplateType::TYPE_PRODUCT) {
                $html =
                    $this->getVariableProductCustomButtonHtml().
                    $this->getVariableButtonHtml() .
                    $barcode .
                    $this->getVariableDependButtonHtml() .
                    $this->getVariableCurrencyButtonHtml();
                return $html;
            }

            $html =
                $this->getSourceButtonHtml() .
                $this->getVariableButtonHtml() .
                $barcode .
                $this->getVariableDependButtonHtml() .
                $this->getVariableCurrencyButtonHtml() .
                $this->getVariableItemsButtonHtml() .
                $this->getVariableOrderItemsButtonHtml() .
                $this->getVariableItemsProductButtonHtml() .
                $this->getVariableCustomerButtonHtml() .
                $this->getVariableOrderButtonHtml();
        }

        return $html;
    }

    /**
     * Return wysiwyg button html
     *
     * @return string
     */
    private function getVariableButtonHtml()
    {

        $html_id = $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'variable_button1',
                    'label' => __('Standard Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/standard',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html
     *
     * @return string
     */
    private function getSourceButtonHtml()
    {

        $html_id = $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'barcode_button2',
                    'label' => __('Source Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/source',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html for the barcodes
     *
     * @return string
     */
    private function getVariableBarcodesButtonHtml()
    {

        $html_id =  $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'barcode_button',
                    'label' => __('Source Barcode Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/barcodes',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html for the depend evaluates if 0 values
     *
     * @return string
     */
    private function getVariableDependButtonHtml()
    {

        $html_id =  $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'variable_button2',
                    'label' => __('Source Depend Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/depend',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html for the depend evaluates if 0 values
     *
     * @return string
     */
    private function getVariableCurrencyButtonHtml()
    {

        $html_id =  $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'variable_button3',
                    'label' => __('Source Currency Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/currency',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html for the depend evaluates if 0 values
     *
     * @return string
     */
    private function getVariableItemsButtonHtml()
    {

        $html_id =  $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'variable_button3',
                    'label' => __('Source Items Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/items',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html for the depend evaluates if 0 values
     *
     * @return string
     */
    private function getVariableItemsProductButtonHtml()
    {

        $html_id =  $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'variable_button3',
                    'label' => __('Source Order Items Product Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/product',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html for the depend evaluates if 0 values
     *
     * @return string
     */
    private function getVariableProductCustomButtonHtml()
    {

        $html_id =  $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'variable_button3',
                    'label' => __('Source Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/productcustom',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html for the depend evaluates if 0 values
     *
     * @return string
     */
    private function getVariableOrderItemsButtonHtml()
    {

        $html_id =  $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'variable_button3',
                    'label' => __('Order Items Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/orderitem',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html for the depend evaluates if 0 values
     *
     * @return string
     */
    private function getVariableCustomerButtonHtml()
    {

        $html_id =  $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'variable_button4',
                    'label' => __('Customer Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/customer',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }

    /**
     * Return wysiwyg button html for the depend evaluates if 0 values
     *
     * @return string
     */
    private function getVariableOrderButtonHtml()
    {

        $html_id =  $this->_element->getData('name');

        $button = $this->getLayout()->createBlock(
            Button::class,
            '',
            [
                'data' => [
                    'name' => 'variable_button5',
                    'label' => __('Order Variables'),
                    'type' => 'button',
                    'style' => ' margin-top:10px; margin-bottom:10px',
                    'class' => 'action-wysiwyg',
                    'onclick' => 'XteaVariablePlugin.loadChooser(\'' .
                        $this->url->getUrl(
                            'eadesign_pdf/variable/order',
                            ['template_id' => $this->pdfModel->getData('template_id')]
                        ) .
                        '\', \'' . $html_id . '\');',
                ]
            ]
        )->toHtml();

        return $button;
    }
}
