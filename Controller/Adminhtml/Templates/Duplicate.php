<?php

namespace Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates;

use Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates;
use Eadesigndev\PdfGeneratorPro\Model\Pdfgenerator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplateRepository;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory;

/**
 * Class Save
 *
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Duplicate extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eadesigndev_PdfGeneratorPro::templates';

    /**
     * @var PdfDataProcessor
     */
    private $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var TemplateRepository
     */
    private $templateRepository;

    /**
     * @var PdfgeneratorFactory
     */
    private $pdfgeneratorFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param PdfDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param TemplateRepository $templateRepository
     * @param PdfgeneratorFactory $pdfgeneratorFactory
     */
    public function __construct(
        Context $context,
        PdfDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        TemplateRepository $templateRepository,
        PdfgeneratorFactory $pdfgeneratorFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->templateRepository = $templateRepository;
        $this->pdfgeneratorFactory = $pdfgeneratorFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /** @var Pdfgenerator $model */

        $id = $this->getRequest()->getParam('template_id');
        if ($id) {
            $model = $this->templateRepository->getById($id);
            $newModel = $this->pdfgeneratorFactory->create();
        }

        $model->unsetData('template_id');
        $newModel->setData($model->getData());

        $newModel->setData('update_time', time());

        try {
            $this->templateRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the template.'));
            $this->dataPersistor->clear('pdfgenerator_template');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving the template.')
            );
        }

        return $resultRedirect->setPath(
            '*/*/edit',
            ['template_id' => $model->getTemplateId(), '_current' => true]
        );
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    //@codingStandardsIgnoreLine
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(Templates::ADMIN_RESOURCE_VIEW);
    }
}
