<?php

namespace Eadesigndev\PdfGeneratorPro\Helper\Variable;

use Eadesigndev\PdfGeneratorPro\Helper\AbstractPDF;
use IntlDateFormatter;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;

class Formated extends AbstractHelper
{

    /**
     * @var Order
     */
    private $order;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var DataObjectFactory
     */
    private $dataObject;

    /**
     * Formated constructor.
     * @param Context $context
     * @param Order $order
     * @param TimezoneInterface $timezoneInterface
     * @param DateTime $dateTime
     * @param DataObjectFactory $dataObject
     */
    public function __construct(
        Context $context,
        Order $order,
        TimezoneInterface $timezoneInterface,
        DateTime $dateTime,
        DataObjectFactory $dataObject
    ) {
        $this->order = $order;
        $this->dateTime = $dateTime;
        $this->timezoneInterface = $timezoneInterface;
        $this->dataObject = $dataObject;
        parent::__construct($context);
    }

    /**
     * Insert the actual order to process the variables
     * @param Object $source
     * @return Order
     */
    public function applySourceOrder($source)
    {

        if (!$source instanceof Order) {
            return $this->order = $source->getOrder();
        }

        return $this->order = $source;
    }

    /**
     * Process object values for pdf output.
     * @param Object $object
     * @return \Magento\Framework\DataObject|null
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function getFormated($object)
    {

        if (!is_object($object)) {
            return null;
        }

        $objectData = $object->getData();

        $newData = [];
        foreach ($objectData as $data => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            if (is_numeric($value) && !is_infinite($value) && $data !== 'increment_id') {
                $newData[$data] = strip_tags($this->order->formatPrice($value));

                if ($data == 'qty' || strpos($data, 'qty') !== false) {
                    $newData[$data] = $value * 1;
                    continue;
                }

                continue;
            }

            if (in_array($data, AbstractPDF::DATE_FIELDS)) {
                $newData[$data] = $this->timezoneInterface->formatDate(
                    $this->timezoneInterface->date($this->dateTime->date($value)),
                    IntlDateFormatter::MEDIUM,
                    false
                );
                continue;
            } else {
                $newData[$data] = $value;
            }
        }

        $eaInvoice = $this->dataObject->create($newData);

        return $eaInvoice;
    }

    /**
     * @param Object $object
     * @param $type
     * @return \Magento\Framework\DataObject|null
     */
    public function getBarcodeFormated($object, $type)
    {
        if (!is_object($object)) {
            return null;
        }

        $objectData = $object->getData();

        $newData = [];
        foreach ($objectData as $data => $value) {
            if (is_numeric($value) || is_string($value)) {
                $newData[$data] = strip_tags($value);
                $newData[$data] = '<barcode code="' .
                    strip_tags($value) .
                    '" type="' .
                    $type .
                    '" size="0.8" class="barcode" text="1" />';
                continue;
            }
        }

        $eaInvoice = $this->dataObject->create($newData);

        return $eaInvoice;
    }

    /**
     * @param Object $object
     * @return \Magento\Framework\DataObject|null
     */
    public function getZeroFormated($object)
    {
        if (!is_object($object)) {
            return null;
        }

        $objectData = $object->getData();

        $newData = [];
        foreach ($objectData as $data => $value) {
            if (is_numeric($value)) {
                if ($value != 0) {
                    $newData[$data] = $value;
                    continue;
                }
            }
        }

        $eaInvoice = $this->dataObject->create($newData);

        return $eaInvoice;
    }

    /**
     * @param $template
     * @param $start
     * @param $end
     * @return array
     * @codingStandardsIgnoreLine
     * and add the validation or there will be a fatal error without the items
     */
    public function getItemsArea($template, $start, $end)
    {

        if (strpos($template, $start) === false) {
            return [$template, '', ''];
        }

        if (strpos($template, $end) === false) {
            return [$template, '', ''];
        }

        $firstPart = explode($start, $template);

        $beginning = $firstPart[0];

        $secondPart = explode($end, $firstPart[1]);

        $items = $secondPart[0];

        $end = $secondPart[1];

        return [$beginning, $items, $end];
    }
}
