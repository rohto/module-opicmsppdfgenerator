<?php

namespace Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea;

use Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Pdfgenerator\Edit\DuplicateButton;
use Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Pdfgenerator\Edit\SaveAndContinueButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Eadesigndev\PdfGeneratorPro\Block\Adminhtml\Xtea
 */
class Edit extends Container
{

    private $duplicateButton;

    /**
     * Core registry
     *
     * @var Registry
     */
    private $coreRegistry = null;

    public function __construct(
        Context $context,
        Registry $registry,
        DuplicateButton $duplicateButton,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->duplicateButton = $duplicateButton;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return void
     */
    public function _construct()
    {
        $this->_objectId = 'template_id';
        $this->_blockGroup = 'Eadesigndev_PdfGeneratorPro';
        $this->_controller = 'adminhtml_xtea';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save template'));
        $this->buttonList->add(
            'duplicate',
            $this->duplicateButton->getButtonData()
        );

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );

        $this->buttonList->update(
            'delete',
            'label',
            __('Delete Template')
        );
    }
}
