<?php

namespace Eadesigndev\PdfGeneratorPro\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

abstract class AbstractSource implements OptionSourceInterface
{

    /**
     * Statuses
     */
    const IS_DEFAULT = 1;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getAvailable();
        foreach ($availableOptions as $key => $value) {
            $options[$key] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
