<?php

namespace Eadesigndev\PdfGeneratorPro\Model\Source;

use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;

/**
 * Class PageLayout
 */
class TemplatePaperOrientation extends AbstractSource
{
    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    private $pageLayoutBuilder;

    /**
     * Constructor
     *
     * @param BuilderInterface $pageLayoutBuilder
     */
    public function __construct(BuilderInterface $pageLayoutBuilder)
    {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    /**
     * Paper types
     */
    const TEMAPLATE_PAPER_PORTRAIT = 1;
    const TEMAPLATE_PAPER_LANDSCAPE = 2;

    /**
     * @return array
     */
    public function getAvailable()
    {
        return [
            self::TEMAPLATE_PAPER_PORTRAIT => 'Portrait',
            self::TEMAPLATE_PAPER_LANDSCAPE => 'Landscape',
        ];
    }
}
