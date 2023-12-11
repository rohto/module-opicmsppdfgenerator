<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Index;

use Eadesigndev\PdfGeneratorPro\Api\TemplatesRepositoryInterface;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors\Output;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors\ProductOutput;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\DataObject\Factory as DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Product
 *
 * @package Eadesigndev\PdfGeneratorPro\Controller\Product
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Product extends Action
{

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var Output
     */
    private $helper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @var
     */
    private $resultForwardFactory;

    /**
     * @var Session
     */
    private $customerSession;

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
     * Product constructor.
     * @param Context $context
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ProductOutput $helper
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Session $customerSession
     * @param ForwardFactory $resultForwardFactory
     * @param DataObject $dataObject
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param TemplatesRepositoryInterface $templatesRepositoryInterface
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        ProductOutput $helper,
        DateTime $dateTime,
        FileFactory $fileFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Session $customerSession,
        ForwardFactory $resultForwardFactory,
        DataObject $dataObject,
        CustomerRepositoryInterface $customerRepositoryInterface,
        TemplatesRepositoryInterface $templatesRepositoryInterface
    ) {
        parent::__construct($context);
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->helper = $helper;
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->customerSession = $customerSession;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->dataObject = $dataObject;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->templatesRepositoryInterface = $templatesRepositoryInterface;
    }

    /**
     * @return ResponseInterface
     */
    public function execute()
    {
        $templateId = $this->getRequest()->getParam('template_id');

        if (!$templateId) {
            $this->noRoute();
        }

        $templateModel = $this->templatesRepositoryInterface
            ->getById($templateId);

        if (!$templateModel) {
            $this->noRoute();
        }

        $collection = $this->collection();

        if (empty($collection)) {
            $this->noRoute();
        }

        $helper = $this->helper;

        $pdfFileData = ['filename'];

        foreach ($collection->getItems() as $source) {
            $helper->setSource($source);
            $helper->setTemplate($templateModel);

            $pdfFileData = $helper->template2Pdf();
        }

        $output = $helper->PDFmerger();

        $fileName = $pdfFileData['filename'] . '.pdf';

        $file = $this->fileFactory->create(
            $fileName,
            $output,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );

        return $file;
    }

    /**
     * @return mixed
     */
    private function collection()
    {
        $this->criteriaBuilder->addFilters(
            [$this->filterBuilder
                ->setField('entity_id')
                ->setValue($this->getRequest()->getParam('product_id'))
                ->setConditionType('eq')
                ->create()]
        );
        $searchCriteria = $this->criteriaBuilder->create();
        //@codingStandardsIgnoreLine
        $collection = $this->_objectManager->create(
            'Magento\Catalog\Api\ProductRepositoryInterface'
        )->getList($searchCriteria);

        return $collection;
    }

    /**
     * @return Forward
     */
    private function noRoute()
    {
        return $this->resultForwardFactory
            ->create()
            ->forward('noroute');
    }
}
