<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\System\Config;

use Eadesigndev\PdfGeneratorPro\Model\Files\Syncrionization;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Syncronize
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\System\Config
 */
class Syncronize extends Action
{

    /**
     * @var Syncrionization
     */
    private $syncrionization;

    /**
     * @var Context
     */
    private $context;

    /**
     * Syncronize constructor.
     * @param Context $context
     * @param Syncrionization $syncrionization
     */
    public function __construct(
        Context $context,
        Syncrionization $syncrionization
    ) {
        $this->syncrionization = $syncrionization;
        $this->context = $context;
        parent::__construct($context);
    }

    /**
     * Here we execute the folder syncronization
     */
    public function execute()
    {
        $this->syncrionization->syncronizeData();
        $this->_redirect($this->_redirect->getRefererUrl());
        $this->context->getMessageManager()->addSuccessMessage(__('Sincronization finished'));
    }
}
