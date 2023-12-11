<?php

namespace Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors;

use Eadesigndev\PdfGeneratorPro\Helper\Variable\Formated;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplatePaperForm;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplatePaperOrientation;
use Eadesigndev\PdfGeneratorPro\Model\Template\Processor;
use Eadesigndev\PdfGeneratorPro\Model\Files\Syncrionization;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\SalesCollect as TaxHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Zend_Pdf;
use Zend_Pdf_Resource_Extractor;
use Mpdf\Mpdf;

/**
 * Class Output
 *
 * @package Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors
 */
class Output extends Pdf
{
    /**
     * @var array
     */
    private $PDFFiles = [];

    /**
     * @var Zend_Pdf
     */
    private $zendPdf;

    /**
     * @var Zend_Pdf_Resource_Extractor
     */
    private $zendExtractor;

    /**
     * @var Syncronize
     */
    private $synchronization;

    /**
     * @var TemplatePaperForm
     */
    private $templatePaperForm;

    /**
     * @var TemplatePaperOrientation
     */
    private $templatePaperOrientation;

    /**
     * Output constructor.
     * @param Context $context
     * @param File $file
     * @param DirectoryList $directoryList
     * @param Processor $processor
     * @param Data $paymentHelper
     * @param InvoiceIdentity $identityContainer
     * @param Renderer $addressRenderer
     * @param Formated $formated
     * @param Items $items
     * @param Zend_Pdf $zendPdf
     * @param Zend_Pdf_Resource_Extractor $zendExtractor
     * @param Syncrionization $syncrionization
     * @param TemplatePaperForm $templatePaperForm
     * @param TemplatePaperOrientation $templatePaperOrientation
     * $pa
     */
    public function __construct(
        Context $context,
        File $file,
        DirectoryList $directoryList,
        Processor $processor,
        Data $paymentHelper,
        InvoiceIdentity $identityContainer,
        Renderer $addressRenderer,
        Formated $formated,
        Items $items,
        Zend_Pdf $zendPdf,
        Zend_Pdf_Resource_Extractor $zendExtractor,
        Syncrionization $syncrionization,
        TemplatePaperForm $templatePaperForm,
        TemplatePaperOrientation $templatePaperOrientation,
        TaxHelper $taxHelper
    ) {
        $this->zendPdf                  = $zendPdf;
        $this->zendExtractor            = $zendExtractor;
        $this->synchronization          = $syncrionization;
        $this->templatePaperForm        = $templatePaperForm;
        $this->templatePaperOrientation = $templatePaperOrientation;
        parent::__construct(
            $context,
            $file,
            $directoryList,
            $processor,
            $paymentHelper,
            $identityContainer,
            $addressRenderer,
            $formated,
            $items,
            $taxHelper
        );
    }

    /**
     * @param $parts
     * @return string
     * @deprecated
     */
    public function _eaPDFSettings($parts)
    {
        $templateModel = $this->template;

        if (!$this->synchronization->isInSync()) {
            $this->synchronization->syncronizeData();
        }

        $oldErrorReporting = error_reporting();
        error_reporting(0);
        //@codingStandardsIgnoreLine
        $pdf = new mPDF([]);

        if (!$templateModel->getTemplateCustomForm()) {
            $pdf = $this->standardSizePdf($templateModel);
        }

        if ($templateModel->getTemplateCustomForm()) {
            $pdf = $this->customSizePdf($templateModel);
        }

        // solve the bug with table body background white
        $header = str_replace(['<tbody>', '</tbody>'], '', $parts['header']);
        $footer = str_replace(['<tbody>', '</tbody>'], '', $parts['footer']);

        //@codingStandardsIgnoreStart
        $pdf->SetHTMLHeader(html_entity_decode($header));
        $pdf->SetHTMLFooter(html_entity_decode($footer));

        $pdf->WriteHTML($templateModel->getTemplateCss(), 1);

        $pdf->WriteHTML('<body>' . html_entity_decode($parts['body']) . '</body>');
        //@codingStandardsIgnoreEnd
        $tmpFile = $this->directoryList->getPath('tmp') .
            DIRECTORY_SEPARATOR .
            $templateModel->getId() .
            $this->source->getEntityType() . $this->source->getIncrementId() .
            '.pdf';

        $this->PDFFiles[] = $tmpFile;

        $pdf->Output($tmpFile, 'F');
        error_reporting($oldErrorReporting);
        return null;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    public function PDFmerger()
    {
        $files = $this->PDFFiles;
        return $this->generateMergeZend($files);
    }

    /**
     * @param $templateModel
     * @param string $finalOri
     * @return Mpdf
     * @throws \Mpdf\MpdfException
     */
    private function standardSizePdf($templateModel, $finalOri = 'P')
    {
        $ori = $templateModel->getTemplatePaperOri();
        $orientation = $this->templatePaperOrientation->getAvailable();
        $finalOri = $orientation[$ori][0];

        $marginTop = $templateModel->getTemplateCustomT();
        $marginBottom = $templateModel->getTemplateCustomB();

        $paperForms = $this->templatePaperForm->getAvailable();

        $templatePaperForm = $templateModel->getTemplatePaperForm();

        if (!$templatePaperForm) {
            $templatePaperForm = 1;
        }

        $form = $paperForms[$templatePaperForm];
        if ($ori == TemplatePaperOrientation::TEMAPLATE_PAPER_LANDSCAPE) {
            $form = $paperForms[$templateModel->getTemplatePaperForm()] . '-' . $finalOri;
        }

        $config = [
            'mode' => '',
            'format' => $form,
            'default_font_size' => '',
            'default_font' => '',
            'margin_left' => $templateModel->getTemplateCustomL(),
            'margin_right' => $templateModel->getTemplateCustomR(),
            'margin_top' => $marginTop,
            'margin_bottom' => $marginBottom,
            'margin_header' => 0,
            'margin_footer' => 0,
            'tempDir' => $this->directoryList->getPath('tmp')
        ];

        //@codingStandardsIgnoreLine
        $pdf = new Mpdf($config);

        return $pdf;
    }

    /**
     * @param $templateModel
     * @return Mpdf
     * @throws \Mpdf\MpdfException
     */
    private function customSizePdf($templateModel)
    {
        $marginTop = $templateModel->getTemplateCustomT();
        $marginBottom = $templateModel->getTemplateCustomB();

        $config = [
            'mode' => '',
            'format' => [
                $templateModel->getTemplateCustomW(),
                $templateModel->getTemplateCustomH()
            ],
            'default_font_size' => '',
            'default_font' => '',
            'margin_left' => $templateModel->getTemplateCustomL(),
            'margin_right' => $templateModel->getTemplateCustomR(),
            'margin_top' => $marginTop,
            'margin_bottom' => $marginBottom,
            'margin_header' => 0,
            'margin_footer' => 0,
            'tempDir' => $this->directoryList->getPath('tmp')
        ];

        //@codingStandardsIgnoreLine
        $pdf = new Mpdf($config);

        return $pdf;
    }

    /**
     * @param $files
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    private function generateMergeZend($files)
    {
        $pdfNew = $this->zendPdf;
        foreach ($files as $file) {
            //@codingStandardsIgnoreLine
            $pdf = Zend_Pdf::load($file);
            $extractor = $this->zendExtractor;
            foreach ($pdf->pages as $page) {
                $pdfExtract = $extractor->clonePage($page);
                $pdfNew->pages[] = $pdfExtract;
            }
        }

        $pdfToOutput = $pdfNew->render();

        foreach ($files as $fileName) {
            $this->file->deleteFile($fileName);
        }

        $pdfNew->pages = [];
        $this->PDFFiles = [];

        return $pdfToOutput;
    }
}
