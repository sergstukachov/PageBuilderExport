<?php

namespace SkillUp\PageBuilderExport\Setup;

use SkillUp\PageBuilderExport\Model\ResourceModel\DataVersion;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /** @var \SkillUp\PageBuilderExport\Helper\Data */
    protected $_helper;

    /** @var \Magento\Framework\Filesystem\Driver\File */
    protected $_filesystemDriver;

    /**
     * UpgradeSchema constructor.
     *
     * @param \Magento\Framework\Filesystem\Driver\File $filesystemDriver
     * @param \SkillUp\PageBuilderExport\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystemDriver,
        \SkillUp\PageBuilderExport\Helper\Data $helper
    ) {
        $this->_filesystemDriver = $filesystemDriver;
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $setup->getTable('skillup_templates_upgrade_data_version');
            if (!$setup->getConnection()->isTableExists($tableName)) {
                $table = $installer->getConnection()->newTable(
                    $tableName
                )->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'data_version',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [],
                    'Data Version'
                );
                $installer->getConnection()->createTable($table);
                $setup->getConnection()->insert($tableName, ['data_version' => '0.0.1']);
                $installer->endSetup();
            }

            $tableName = $setup->getTable(DataVersion::INSTALLED_FILES_TABLE);
            if (!$setup->getConnection()->isTableExists($tableName)) {
                $table = $installer->getConnection()->newTable(
                    $tableName
                )->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'file',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    [],
                    'Templates Upgrade file'
                );
                $installer->getConnection()->createTable($table);

                if ($this->_filesystemDriver->isExists($this->_helper->getModuleSetupDataDir())) {
                    $installedFiles = $this->_filesystemDriver->readDirectory($this->_helper->getModuleSetupDataDir());
                    foreach ($installedFiles as $installedFile) {
                        $setup->getConnection()->insert($tableName, ['file' => basename($installedFile)]);
                    }
                } else {
                    //Create directory
                    try {
                        $this->_filesystemDriver->createDirectory($this->_helper->getModuleSetupDataDir());
                    } catch (\Throwable $exception) {
                        $installer->endSetup();
                        return;
                    }
                }

                $installer->endSetup();
            }
        }
    }
}
