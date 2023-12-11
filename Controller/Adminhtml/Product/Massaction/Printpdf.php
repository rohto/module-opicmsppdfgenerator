<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Product\Massaction;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplatesRepositoryInterface;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors\ProductOutput as OutputHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
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
class Printpdf extends AbstractMassAction
{
    private $productCollectionFactory;

    /**
     * @var $outputHelper
     */
    public $outputHelper;

    /**
     * Printpdf constructor.
     * @param Context $context
     * @param Filter $filter
     * @param OrderCollectionFactory $collectionFactory
     * @param FileFactory $fileFactory
     * @param DateTime $dateTime
     * @param OutputHelper $outputHelper
     * @param ForwardFactory $resultForwardFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param DataObject $dataObject
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param TemplatesRepositoryInterface $templatesRepositoryInterface
     * @param PdfgeneratorFactory $pdfgeneratorFactory
     * @SuppressWarnings(ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Filter $filter,
        OrderCollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        DateTime $dateTime,
        OutputHelper $outputHelper,
        ForwardFactory $resultForwardFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        DataObject $dataObject,
        CustomerRepositoryInterface $customerRepositoryInterface,
        TemplatesRepositoryInterface $templatesRepositoryInterface,
        PdfgeneratorFactory $pdfgeneratorFactory,
        CollectionFactory $productCollectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
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
            $pdfgeneratorFactory
        );
    }

    /**
     * @param AbstractCollection $collection
     * @return ResponseInterface
     */
    //@codingStandardsIgnoreLine
    protected function massAction(AbstractCollection $collection)
    {
        $collection = $this->filter->getCollection(
            $this->productCollectionFactory->create()->addAttributeToSelect('*')
        );

        $this->abstractCollection = $collection;
        $this->generateFile();

        return null;
    }
}
