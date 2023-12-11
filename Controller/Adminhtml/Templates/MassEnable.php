<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates;

use Magento\Framework\Controller\ResultFactory;

class MassEnable extends MassAction
{

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->templateCollectionFactory->create());

        foreach ($collection as $item) {
            $item->setIsActive(true);
            //@codingStandardsIgnoreLine
            $item->save();
        }

        $this->messageManager->addSuccessMessage(
            __(
                'A total of %1 record(s) have been enabled.',
                $collection->getSize()
            )
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
