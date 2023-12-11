<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Massaction;

use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplatesRepositoryInterface;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors\Output as OutputHelper;
use Eadesigndev\PdfGeneratorPro\Helper\Data as DataHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as ChildCollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\DataObject\Factory as DataObject;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Printpdf
 *
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Massaction
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class PrintpdfInvoice extends AbstractMassAction
{
    /**
     * @var ChildCollectionFactory
     */
    private $childCollectionFactory;

    /**
     * PrintpdfInvoice constructor.
     * @param Context $context
     * @param Filter $filter
     * @param OrderCollectionFactory $collectionFactory
     * @param ChildCollectionFactory $childCollectionFactory
     * @param FileFactory $fileFactory
     * @param DateTime $dateTime
     * @param OutputHelper $outputHelper
     * @param ForwardFactory $resultForwardFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param DataObject $dataObject
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param TemplatesRepositoryInterface $templatesRepositoryInterface
     * @param PdfgeneratorFactory $pdfgeneratorFactory
     * @param DataHelper $dataHelper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        OrderCollectionFactory $collectionFactory,
        ChildCollectionFactory $childCollectionFactory,
        FileFactory $fileFactory,
        DateTime $dateTime,
        OutputHelper $outputHelper,
        ForwardFactory $resultForwardFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        DataObject $dataObject,
        CustomerRepositoryInterface $customerRepositoryInterface,
        TemplatesRepositoryInterface $templatesRepositoryInterface,
        PdfgeneratorFactory $pdfgeneratorFactory,
        DataHelper $dataHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->childCollectionFactory = $childCollectionFactory;
        parent::__construct(
            $context,
            $filter,
            $fileFactory,
            $dateTime,
            $outputHelper,
            $resultForwardFactory,
            $extensibleDataObjectConverter,
            $dataObject,
            $customerRepositoryInterface,
            $templatesRepositoryInterface,
            $pdfgeneratorFactory,
            $dataHelper
        );
    }

    /**
     * @param AbstractCollection $collection
     * @return ResponseInterface
     */
    //@codingStandardsIgnoreLine
    protected function massAction(AbstractCollection $collection)
    {
        $orderIds = $collection->getAllIds();

        $invoices = $this->childCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);

        if (!$invoices->getSize()) {
            $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        $this->abstractCollection = $invoices;
        $this->generateFile();

        return null;
    }
}
