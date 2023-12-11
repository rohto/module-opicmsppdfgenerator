<?php

namespace Eadesigndev\PdfGeneratorPro\Model\Files;

use Eadesigndev\PdfGeneratorPro\Model\Source\TemplateType;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;

/**
 * Class TemplateReader
 * @package Eadesigndev\PdfGeneratorPro\Model
 */
class TemplateReader
{

    const PDF_TEMPLATES_DIR = 'pdftemplates';
    const BODY = 'body';
    const CSS = 'css';
    const HEADER = 'header';
    const FOOTER = 'footer';

    /**
     * @var ModuleDirReader
     */
    private $moduleDirReader;

    /**
     * @var File
     */
    private $file;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * TemplateReader constructor.
     * @param File $file
     * @param DirectoryList $directoryList
     * @param ModuleDirReader $moduleDirReader
     */
    public function __construct(
        File $file,
        DirectoryList $directoryList,
        ModuleDirReader $moduleDirReader
    ) {
        $this->file            = $file;
        $this->directoryList   = $directoryList;
        $this->moduleDirReader = $moduleDirReader;
    }

    /**
     * @return string
     */
    private function templatesLocation()
    {
        $viewDir = $this->moduleDirReader->getModuleDir(
            Dir::MODULE_VIEW_DIR,
            'Eadesigndev_PdfGeneratorPro'
        );
        return $viewDir . DIRECTORY_SEPARATOR . self::PDF_TEMPLATES_DIR;
    }

    /**
     * @return array
     */
    public function directoryParser()
    {
        $templates = $this->htmlTemplates();

        $inserts = [];
        foreach ($templates as $template) {
            $templateName = explode('.', $template);
            $inserts[] = $this->createInsertArray($templateName[0]);
        }

        return $inserts;
    }

    /**
     * @return array
     */
    public function htmlTemplates()
    {
        $path = $this->templatesLocation(). DIRECTORY_SEPARATOR . self::BODY;
        $files = $this->file->readDirectory($path);
        $fileNames = [];

        foreach ($files as $file) {
            //@codingStandardsIgnoreLine
            $fileNames[] = basename($file);
        }

        return $fileNames;
    }

    /**
     * @param $templateName
     * @return array
     */
    private function createInsertArray($templateName)
    {

        $bodyPath = $this->templatesLocation() . DIRECTORY_SEPARATOR . self::BODY . DIRECTORY_SEPARATOR;
        $bodyContents = $this->file->fileGetContents($bodyPath . $templateName . '.html');

        $headerPath = $this->templatesLocation() . DIRECTORY_SEPARATOR . self::HEADER . DIRECTORY_SEPARATOR;
        $headerContents = $this->file->fileGetContents($headerPath . $templateName . '.html');

        $footerPath = $this->templatesLocation() . DIRECTORY_SEPARATOR . self::FOOTER . DIRECTORY_SEPARATOR;
        $footerContents = $this->file->fileGetContents($footerPath . $templateName . '.html');

        $cssPath = $this->templatesLocation() . DIRECTORY_SEPARATOR . self::CSS . DIRECTORY_SEPARATOR;
        $cssContents = $this->file->fileGetContents($cssPath . $templateName . '.css');

        $name = ucfirst(str_replace('_', ' ', $templateName));
        $typeString = explode('_', $templateName);

        if ($typeString[0] != 'product') {
            $sourceId = '000000001';
        } else {
            $sourceId = 10;
        }

        $type = array_flip(TemplateType::TYPES)[$typeString[0]];

        $orientation = 1;
        if ($typeString[2] === 'landscape') {
            $orientation = 2;
        }

        $top = 50;
        $bottom = 20;
        $right = 20;
        $left = 20;

        if ($typeString[1] === 'stylish') {
            $top = 45;
            $bottom = 20;
            $right = 0;
            $left = 0;
        }

        $data = [
            'store_id' => 0,
            'is_active' => 1,
            'template_name' => $name,
            'template_description' => $name,
            'template_default' => 1,
            'template_type' => $type,
            'template_body' => $bodyContents,
            'template_header' => $headerContents,
            'template_footer' => $footerContents,
            'template_css' => $cssContents,
            'template_file_name' => $templateName. '.pdf',
            'template_paper_form' => 1,
            'template_custom_form' => 0,
            'template_custom_h' => 25,
            'template_custom_w' => 25,
            'template_custom_t' => $top,
            'template_custom_b' => $bottom,
            'template_custom_l' => $left,
            'template_custom_r' => $right,
            'template_paper_ori' => $orientation,
            'barcode_types' => 'c39',
            'customer_group_id' => '0',
            'creation_time' => time(),
            'update_time' => time(),
            'attachments' => 1,
            'source' => $sourceId
        ];

        return $data;
    }
}
