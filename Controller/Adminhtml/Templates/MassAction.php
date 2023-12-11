<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates;

use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Eadesigndev\PdfGeneratorPro\Model\ResourceModel\Pdfgenerator\CollectionFactory as templateCollectionFactory;

abstract class MassAction extends Action
{
    /**
     * @var Filter
     */
    public $filter;

    /**
     * @var CollectionFactory
     */
    public $templateCollectionFactory;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param templateCollectionFactory $templateCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        templateCollectionFactory $templateCollectionFactory
    ) {
        $this->filter = $filter;
        $this->templateCollectionFactory = $templateCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    //@codingStandardsIgnoreLine
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            Templates::ADMIN_RESOURCE_SAVE
        );
    }
}
