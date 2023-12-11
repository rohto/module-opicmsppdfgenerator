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
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateActive;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplateRepository;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory;

/**
 * Class Save
 *
 * @package Eadesigndev\PdfGeneratorPro\Controller\Adminhtml\Templates
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Save extends Action
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
        $data = $this->getRequest()->getPostValue();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->dataProcessor->validateRequireEntry($data);
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = TemplateActive::STATUS_ENABLED;
            }

            if (empty($data['template_id'])) {
                $data['template_id'] = null;
            }

            /** @var Pdfgenerator $model */

            $id = $this->getRequest()->getParam('template_id');
            if ($id) {
                $model = $this->templateRepository->getById($id);
            } else {
                unset($data['template_id']);
                $model = $this->pdfgeneratorFactory->create();
            }

            if (empty($data['stores'][0])) {
                $data['store_id']  = [0 => "0"];
            }

            $model->setData($data);

            $model->setData('update_time', time());

            if (isset($data['barcode_types'])) {
                $model->setData('barcode_types', implode(',', $data['barcode_types']));
            }

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect
                    ->setPath('*/*/edit', ['template_id' => $model->getTemplateId(), '_current' => true]);
            }

            try {
                $this->templateRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the template.'));
                $this->dataPersistor->clear('pdfgenerator_template');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect
                        ->setPath('*/*/edit', ['template_id' => $model->getTemplateId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the template.')
                );
            }

            $this->dataPersistor->set('pdfgenerator_template', $data);
            return $resultRedirect
                ->setPath('*/*/edit', ['template_id' => $this->getRequest()->getParam('template_id')]);
        }
        return $resultRedirect->setPath('*/*/');
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
