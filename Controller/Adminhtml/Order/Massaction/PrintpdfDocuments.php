<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Massaction;

use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplatesRepositoryInterface;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors\Output as OutputHelper;
use Eadesigndev\PdfGeneratorPro\Helper\Data as DataHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as CreditmemoCollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\DataObject\Factory as DataObject;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class PrintpdfDocuments
 *
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Massaction
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class PrintpdfDocuments extends AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    public $fileFactory;

    /**
     * @var DateTime
     */
    public $dateTime;

    /**
     * @var OrderCollectionFactory
     */
    public $collectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    private $shipmentCollectionFactory;

    /**
     * @var InvoiceCollectionFactory
     */
    private $invoiceCollectionFactory;

    /**
     * @var CreditmemoCollectionFactory
     */
    private $creditmemoCollectionFactory;

    /**
     * PrintpdfDocuments constructor.
     * @param Context $context
     * @param Filter $filter
     * @param OrderCollectionFactory $collectionFactory
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param CreditmemoCollectionFactory $creditmemoCollectionFactory
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
        InvoiceCollectionFactory $invoiceCollectionFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        CreditmemoCollectionFactory $creditmemoCollectionFactory,
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
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|null
     * @throws \Exception
     */
    public function massAction(AbstractCollection $collection)
    {
        $orderIds = $collection->getAllIds();

        $shipments = $this->shipmentCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
        $invoices = $this->invoiceCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);
        $creditmemos = $this->creditmemoCollectionFactory->create()->setOrderFilter(['in' => $orderIds]);

        if ($invoices->getSize()) {
            foreach ($invoices as $invoiceItem) {
                $lastItemId = $collection->getLastItem()->getId();
                $invoiceItem->setPdfOriginalId($invoiceItem->getId());
                $invoiceItem->setId($lastItemId + 1);
                $collection->addItem($invoiceItem);
            }
        }
        if ($shipments->getSize()) {
            foreach ($shipments as $shipmentItem) {
                $lastItemId = $collection->getLastItem()->getId();
                $shipmentItem->setPdfOriginalId($shipmentItem->getId());
                $shipmentItem->setId($lastItemId + 1);
                $collection->addItem($shipmentItem);
            }
        }
        if ($creditmemos->getSize()) {
            foreach ($creditmemos as $creditmemoItem) {
                $lastItemId = $collection->getLastItem()->getId();
                $creditmemoItem->setPdfOriginalId($creditmemoItem->getId());
                $creditmemoItem->setId($lastItemId + 1);
                $collection->addItem($creditmemoItem);
            }
        }

        if (empty($collection)) {
            $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        $this->abstractCollection = $collection;
        $this->generateFile();

        return null;
    }
}
