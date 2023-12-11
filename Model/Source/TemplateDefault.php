<?php

namespace Eadesigndev\PdfGeneratorPro\Model\Source;

class TemplateDefault extends AbstractSource
{
    /**
     * Statuses
     */
    const STATUS_YES = 1;
    const STATUS_NO = 0;

    /**
     * @return array
     */
    public function getAvailable()
    {
        return [self::STATUS_YES => __('Yes'), self::STATUS_NO => __('No')];
    }
}
