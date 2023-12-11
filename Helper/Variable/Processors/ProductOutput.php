<?php

namespace Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors;

use Eadesigndev\PdfGeneratorPro\Helper\Variable\ProductFormated;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplatePaperForm;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplatePaperOrientation;
use Eadesigndev\PdfGeneratorPro\Helper\AbstractPDF;
use Eadesigndev\PdfGeneratorPro\Model\Template\Processor;
use Eadesigndev\PdfGeneratorPro\Model\Files\Syncrionization;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\Product as CustomProduct;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Zend_Pdf;
use Zend_Pdf_Resource_Extractor;
use Mpdf\Mpdf;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * Class Output
 *
 * @package Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors
 */
class ProductOutput extends ProductPdf
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
     * @var CustomProduct
     */
    private $customProduct;

    /**
     * @var TemplatePaperForm
     */
    private $templatePaperForm;

    /**
     * @var TemplatePaperOrientation
     */
    private $templatePaperOrientation;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * ProductOutput constructor.
     * @param Context $context
     * @param File $file
     * @param DirectoryList $directoryList
     * @param Processor $processor
     * @param Data $paymentHelper
     * @param InvoiceIdentity $identityContainer
     * @param Renderer $addressRenderer
     * @param ProductFormated $formated
     * @param Items $items
     * @param Zend_Pdf $zendPdf
     * @param Zend_Pdf_Resource_Extractor $zendExtractor
     * @param Syncrionization $syncrionization
     * @param CustomProduct $customProduct
     * @param TemplatePaperForm $templatePaperForm
     * @param TemplatePaperOrientation $templatePaperOrientation
     */
    public function __construct(
        Context $context,
        File $file,
        DirectoryList $directoryList,
        Processor $processor,
        Data $paymentHelper,
        InvoiceIdentity $identityContainer,
        Renderer $addressRenderer,
        ProductFormated $formated,
        Items $items,
        Zend_Pdf $zendPdf,
        Zend_Pdf_Resource_Extractor $zendExtractor,
        Syncrionization $syncrionization,
        CustomProduct $customProduct,
        TemplatePaperForm $templatePaperForm,
        TemplatePaperOrientation $templatePaperOrientation,
        FilterProvider $filterProvider
    ) {
        $this->synchronization          = $syncrionization;
        $this->zendPdf                  = $zendPdf;
        $this->zendExtractor            = $zendExtractor;
        $this->customProduct            = $customProduct;
        $this->templatePaperForm        = $templatePaperForm;
        $this->templatePaperOrientation = $templatePaperOrientation;
        $this->filterProvider           = $filterProvider;
        parent::__construct(
            $context,
            $file,
            $directoryList,
            $processor,
            $paymentHelper,
            $identityContainer,
            $addressRenderer,
            $formated,
            $items
        );
    }

    /**
     * @param $source
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * This will process the template and the variables from the entity's
     *
     * @return string
     */
    public function _transport()
    {
        $order = $this->order;
        $source = $this->source;

        $templateModel = $this->template;
        $templateType = $templateModel->getData('template_type');

        $templateTypeName = TemplateType::TYPES[$templateType];

        $extendedProduct = $this->customProduct->entity($source)->processAndReadVariables();

        $transport = [
            'product' => $extendedProduct,
            $templateTypeName => $source,
            'store' => $this->source,
            'custom_' . $templateTypeName => $this->formated->getFormated($source),
            'custom_product' => $this->formated->getFormated($source),
            $templateTypeName . '_if' => $this->formated->getZeroFormated($source),
            'product_if' => $this->formated->getZeroFormated($source),
        ];

        foreach (AbstractPDF::CODE_BAR as $code) {
            $transport['custom_barcode_' . $code . '_' . $templateTypeName] = $this->formated->getBarcodeFormated(
                $source,
                $code
            );
            $transport['custom_barcode_' . $code . '_order'] = $this->formated->getBarcodeFormated($order, $code);
            $transport['custom_barcode_' . $code . '_customer'] = $this->formated->getBarcodeFormated(
                $this->customer,
                $code
            );
        }

        /** @var Processor $processor */
        $processor = $this->processor;

        $processor->setVariables($transport);
        $processor->setTemplate($this->template);

        $parts = $processor->processTemplate();

        return $parts;
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
        $pdf = new Mpdf([]);

        if (!$templateModel->getTemplateCustomForm()) {
            $pdf = $this->standardSizePdf($templateModel);
        }

        if ($templateModel->getTemplateCustomForm()) {
            $pdf = $this->customSizePdf($templateModel);
        }

        $filterProvider = $this->filterProvider;

        $partsHeader = $parts['header'];
        $header = str_replace(['<tbody>', '</tbody>'], '', $partsHeader);
        //@codingStandardsIgnoreLine
        $decodeHeader = $pdf->SetHTMLHeader(html_entity_decode($header));
        $filteredHeader = $filterProvider->getPageFilter()->filter($decodeHeader);
        $pdf->WriteHTML('<header>' . $filteredHeader . '</header>');

        $partsFooter = $parts['footer'];
        $footer = str_replace(['<tbody>', '</tbody>'], '', $partsFooter);
        //@codingStandardsIgnoreLine
        $decodeFooter = $pdf->SetHTMLFooter(html_entity_decode($footer));
        $filteredFooter = $filterProvider->getPageFilter()->filter($decodeFooter);
        $pdf->WriteHTML('<footer>' . $filteredFooter . '</footer>');

        $pdf->WriteHTML($templateModel->getTemplateCss(), 1);
        //@codingStandardsIgnoreLine
        $decodeBody = html_entity_decode($parts['body']);
        $content = $parts['body'];
        $filteredBoby = $filterProvider->getPageFilter()->filter($decodeBody);

        $pdf->WriteHTML('<body>' . $filteredBoby . '</body>');
        //@codingStandardsIgnoreEnd
        $tmpFile = $this->directoryList->getPath('tmp') .
            DIRECTORY_SEPARATOR .
            $this->source->getId() .
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

        return $pdfToOutput;
    }
}
