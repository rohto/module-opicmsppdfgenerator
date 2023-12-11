<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates;

use Eadesigndev\PdfGeneratorPro\Model\Files\Syncrionization;
use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Index extends Templates
{

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Syncrionization
     */
    public $synchronization;

    /**
     * @var Context
     */
    public $context;

    /**
     * Index constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param Syncrionization $synchronization
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Syncrionization $synchronization
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->synchronization = $synchronization;
        $this->context = $context;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $message = '';
        if (!$this->synchronization->isInSync()) {
            $message = __('You need to synchronize the folders from system configuration');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('PDF Templates %1 ', $message));

        return $resultPage;
    }
}
