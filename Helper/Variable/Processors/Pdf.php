<?php

namespace Eadesigndev\PdfGeneratorPro\Helper\Variable\Processors;

use Eadesigndev\PdfGeneratorPro\Helper\Variable\Formated;
use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Eadesigndev\PdfGeneratorPro\Model\Template\Processor;
use Eadesigndev\PdfGeneratorPro\Helper\AbstractPDF;
use Eadesigndev\PdfGeneratorPro\Helper\Variable\Custom\SalesCollect as TaxHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;

/**
 * Class Pdf
 * Process the variable so they are configured for pdf output
 *
 * @package Eadesigndev\PdfGeneratorPro\Helper
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Pdf extends AbstractPDF
{

    /**
     * @var File
     */
    public $file;

    /**
     * @var DirectoryList
     */
    public $directoryList;

    /**
     * @var Formated
     */
    public $formated;

    /**
     * @var Items
     */
    private $items;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * Pdf constructor.
     * @param Context $context
     * @param File $file
     * @param DirectoryList $directoryList
     * @param Processor $processor
     * @param Data $paymentHelper
     * @param InvoiceIdentity $identityContainer
     * @param Renderer $addressRenderer
     * @param Formated $formated
     * @param Items $items
     * @param TaxHelper $taxHelper
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
        TaxHelper $taxHelper
    ) {
        $this->file          = $file;
        $this->directoryList = $directoryList;
        $this->formated      = $formated;
        $this->items         = $items;
        $this->taxHelper     = $taxHelper;
        parent::__construct($context, $processor, $paymentHelper, $identityContainer, $addressRenderer);
    }

    /**
     * Filename of the pdf and the stream to sent to the download
     *
     * @return array
     */
    public function template2Pdf()
    {
        $source = $this->source;
        $templateModel = $this->template;

        $this->formated->applySourceOrder($source);

        $itemHtml = $this->items->processItems($source, $templateModel);

        $templateModel->setData('template_body', $itemHtml);

        /**transport use to get the variables $order object, $source object and the template model object*/
        $parts = $this->_transport();

        /** instantiate the mPDF class and add the processed html to get the pdf*/

        /** @var Output $applySettings */
        $applySettings = $this->_eapdfSettings($parts);

        $fileParts = [
            'model' => $templateModel,
            'filestream' => $applySettings,
            'filename' => filter_var($parts['filename'], FILTER_SANITIZE_URL)
        ];

        return $fileParts;
    }

    /**
     * This will process the template and the variables from the entity's
     *
     * @return string
     */
    public function _transport()
    {
        $order = $this->taxHelper->entity($this->order)->processAndReadVariables();
        $source = $this->taxHelper->entity($this->source)->processAndReadVariables();

        $templateModel = $this->template;
        $templateType = $templateModel->getData('template_type');

        $templateTypeName = TemplateType::TYPES[$templateType];

        $transport = [
            'order' => $this->formated->getFormated($order),
            $templateTypeName => $source,
            'customer' => $this->customer,
            'comment' => $source->getCustomerNoteNotify() ? $source->getCustomerNote() : '',
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),

            'custom_' . $templateTypeName => $this->formated->getFormated($source),
            'custom_order' => $this->formated->getFormated($order),
            $templateTypeName . '_if' => $this->formated->getZeroFormated($source),
            'order_if' => $this->formated->getZeroFormated($order),

            'customer_if' => $this->formated->getZeroFormated($this->customer),

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
}
