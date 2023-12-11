<?php

namespace Eadesigndev\PdfGeneratorPro\Model\Plugin;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Registry;

class Config
{

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * Config constructor.
     * @param UrlInterface $url
     * @param Registry $registry
     */
    public function __construct(
        UrlInterface $url,
        Registry $registry
    ) {
        $this->url = $url;
        $this->registry = $registry;
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    //@codingStandardsIgnoreLine
    public function afterGetVariablesWysiwygActionUrl($subject, $result)
    {
        if ($this->registry->registry('pdfgenerator_template')) {
            return $this->getUrl();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url->getUrl('eadesign_pdf/variable/template');
    }
}
