<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Massaction;

use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplatesRepositoryInterface;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors\Output as OutputHelper;
use Eadesigndev\PdfGeneratorPro\Helper\Data as DataHelper;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction as SalesAbstractMassAction;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Controller\Result\Forward;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\DataObject\Factory as DataObject;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;

/**
 * Class AbstractMassAction
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Order\Massaction
 * @SuppressWarnings(CouplingBetweenObjects)
 */
abstract class AbstractMassAction extends SalesAbstractMassAction
{
    /**
     * @var $collectionFactory
     */
    public $collectionFactory;

    /**
     * @var FileFactory
     */
    public $fileFactory;

    /**
     * @var DateTime
     */
    public $dateTime;

    /**
     * @var $outputHelper
     */
    public $outputHelper;

    /**
     * @var ForwardFactory
     */
    public $resultForwardFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    public $extensibleDataObjectConverter;

    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var TemplatesRepositoryInterface
     */
    private $templatesRepositoryInterface;

    /**
     * @var AbstractCollection
     */
    public $abstractCollection;

    /**
     * @var PdfgeneratorFactory
     */
    public $pdfgeneratorFactory;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * AbstractMassAction constructor.
     * @param Context $context
     * @param Filter $filter
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
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->outputHelper = $outputHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->dataObject = $dataObject;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->templatesRepositoryInterface = $templatesRepositoryInterface;
        $this->pdfgeneratorFactory = $pdfgeneratorFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $filter);
    }

    /**
     * @param $templateId
     * @param $source
     * @return object
     */
    public function processCollection($source, $templateId = null)
    {
        if ($id = $source->getPdfOriginalId()) {
            $source->setId($id);
        }

        if ($templateId === null) {
            $types = array_flip(TemplateType::TYPES);
            $entityType = $source->getEntityType();
            /** @var DataHelper $lastItem */
            $templateId = $this->dataHelper->getTemplateStatus(
                $source,
                $types[$entityType]
            )->getId();
        }

        $templateModel = $this->pdfgeneratorFactory->create()->load($templateId);

        if (!$templateModel) {
            $this->noRoute();
        }

        $helper = $this->outputHelper;
        $helper->setSource($source);
        $helper->setTemplate($templateModel);

        if ($customerId = $source->getCustomerId()) {
            $pseudoCustomer = $this->customer($customerId);
            $helper->setCustomer($pseudoCustomer);
        }

        $helper->template2Pdf();

        return $templateModel;
    }

    /**
     * @param $customerId
     * @return \Magento\Framework\DataObject;
     */
    public function customer($customerId)
    {
        /** @var Customer $customer */
        $customer = $this->customerRepositoryInterface
            ->getById($customerId);

        $customerData = $this->extensibleDataObjectConverter->toFlatArray(
            $customer,
            [],
            CustomerInterface::class
        );

        $pseudoCustomer = $this->dataObject->create($customerData);

        return $pseudoCustomer;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function generateFile()
    {
        $collection = $this->abstractCollection;

        $templateId = $this->getRequest()->getParam('template_id');

        foreach ($collection as $source) {
            $this->processCollection($source, $templateId);
        }

        $output = $this->outputHelper->PDFmerger();

        $dateTime = $this->dateTime->date('Y-m-d_H-i-s');

        $fileName = 'mass_print_pdf_order' . $dateTime . '.pdf';

        $file = $this->fileFactory->create(
            $fileName,
            $output,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );

        return $file;
    }

    /**
     * @return bool
     */
    //@codingStandardsIgnoreLine
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_order');
    }

    /**
     * @return Forward
     */
    public function noRoute()
    {
        return $this->resultForwardFactory
            ->create()
            ->forward('noroute');
    }
}
