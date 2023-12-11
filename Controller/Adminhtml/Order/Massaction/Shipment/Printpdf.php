<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Massaction\Shipment;

use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Massaction\AbstractMassAction;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplatesRepositoryInterface;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors\Output as OutputHelper;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory;
use Eadesigndev\PdfGeneratorPro\Helper\Data as DataHelper;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as InvoiceCollectionFactory;
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
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Massaction\Shipment
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Printpdf extends AbstractMassAction
{
    /**
     * Printpdf constructor.
     * @param Context $context
     * @param Filter $filter
     * @param InvoiceCollectionFactory $collectionFactory
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
        InvoiceCollectionFactory $collectionFactory,
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
    public function massAction(AbstractCollection $collection)
    {
        $this->abstractCollection = $collection;
        $this->generateFile();

        return null;
    }
}
