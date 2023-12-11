<?php

namespace Eadesigndev\PdfGeneratorPro\Setup;

use Eadesigndev\PdfGeneratorPro\Model\Files\TemplateReader;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorFactory;
use Eadesigndev\PdfGeneratorPro\Model\PdfgeneratorRepository as TemplateRepository;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class InstallData
 * @package Eadesigndev\PdfGeneratorPro\Setup
 * Adds the templates default on module install
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD)
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var PdfgeneratorFactory
     */
    private $templateFactory;

    /**
     * @var TemplateRepository
     */
    private $templateRepository;

    /**
     * @var TemplateReader
     */
    private $templateReader;

    /**
     * InstallData constructor.
     * @param PdfgeneratorFactory $templateFactory
     * @param TemplateRepository $templateRepository
     */
    public function __construct(
        PdfgeneratorFactory $templateFactory,
        TemplateRepository $templateRepository,
        TemplateReader $templateReader
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateRepository = $templateRepository;
        $this->templateReader = $templateReader;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return $this
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $templates = $this->templateReader->directoryParser();

        if (empty($templates)) {
            return $this;
        }

        foreach ($templates as $template) {
            $tmpl = $this->templateFactory->create();
            $tmpl->setData($template);
            //@codingStandardsIgnoreLine
            $this->templateRepository->save($tmpl);
        }
    }
}
