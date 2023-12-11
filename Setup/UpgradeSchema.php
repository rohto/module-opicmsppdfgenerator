<?php

namespace Eadesigndev\PdfGeneratorPro\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;

//@codingStandardsIgnoreFile

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.2.4', '<')) {
            $this->addSource($setup);
        }

        if (version_compare($context->getVersion(), '2.2.3', '<')) {
            $this->addAttachments($setup);
        }

        $setup->endSetup();
    }

    public function addSource(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(InstallSchema::PDF_TABLE),
            'source',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Source id'
            ]
        );
    }

    public function addAttachments(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(InstallSchema::PDF_TABLE),
            'attachments',
            [
                'type' => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Attachments comma separated'
            ]
        );
    }
}
