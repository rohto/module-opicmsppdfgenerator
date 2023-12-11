<?php

namespace Eadesigndev\PdfGeneratorPro\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
//@codingStandardsIgnoreFile
/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    const PDF_TABLE = 'xtea_pdf_templates';

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $pdfGeneratorTable = $setup->getTable(self::PDF_TABLE);

        if (!$setup->tableExists($pdfGeneratorTable)) {
            $this->installBefore($setup);
        }

        $setup->getConnection()->addColumn(
            $pdfGeneratorTable,
            'barcode_types',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Use of barcodes'
            ]
        );

        $setup->getConnection()->addColumn(
            $pdfGeneratorTable,
            'customer_group_id',
            [
                'type' => Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Customer group id the template is for'
            ]
        );

        $setup->endSetup();
    }

    /**
     * @param $installer
     */
    private function installBefore($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('xtea_pdf_templates'))
            ->addColumn(
                'template_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Template Id'
            )
            ->addColumn('is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Template active?')
            ->addColumn('template_name', Table::TYPE_TEXT, 100, ['nullable' => false], 'Template name')
            ->addColumn('template_description', Table::TYPE_TEXT, 500, ['nullable' => false], 'Template description')
            ->addColumn('template_default', Table::TYPE_BOOLEAN, null, ['nullable' => false, 'default' => '0'], 'Template default')
            ->addColumn('template_type', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Template type')
            ->addColumn('template_body', Table::TYPE_TEXT, '2M', [], 'Template body')
            ->addColumn('template_header', Table::TYPE_TEXT, '2M', [], 'Template header')
            ->addColumn('template_footer', Table::TYPE_TEXT, '2M', [], 'Template footer')
            ->addColumn('template_css', Table::TYPE_TEXT, 500, ['nullable' => false], 'Template css')
            ->addColumn('template_file_name', Table::TYPE_TEXT, 100, ['nullable' => false], 'Template file name')
            ->addColumn('template_paper_form', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Paper format')
            ->addColumn('template_custom_form', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Paper custom format')
            ->addColumn('template_custom_h', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template height')
            ->addColumn('template_custom_w', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template width')
            ->addColumn('template_custom_t', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template top margin')
            ->addColumn('template_custom_b', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template bottom margin')
            ->addColumn('template_custom_l', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template left margin')
            ->addColumn('template_custom_r', Table::TYPE_DECIMAL, null, ['nullable' => false, 'default' => '1'], 'Custom template right margin')
            ->addColumn('template_paper_ori', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Paper orientation')
            ->addColumn('creation_time', Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Creation Time')
            ->addColumn('update_time', Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'Update Time')
            ->addIndex($installer->getIdxName('template_id', ['template_id']), ['template_id'])
            ->setComment('Eadesign PDF Generator Templates');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('xtea_pdf_store')
        )->addColumn(
            'template_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Template ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addIndex(
            $installer->getIdxName('xtea_pdf_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('xtea_pdf_store', 'template_id', 'xtea_pdf_templates', 'template_id'),
            'template_id',
            $installer->getTable('xtea_pdf_templates'),
            'template_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('xtea_pdf_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'PDF Generator To Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);
    }

}
