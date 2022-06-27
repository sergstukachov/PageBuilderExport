<?php

namespace SkillUp\PageBuilderExport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DataVersion extends AbstractDb
{
    /**
     * Table name. Save number latest generated file
     */
    public const LATEST_GENERATED_FILE = 'skillup_templates_export_data_version';//'skillup_templates_upgrade_data_version';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::LATEST_GENERATED_FILE, 'id');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadDataVersion()
    {
        $connection = $this->getConnection();
        return $connection->fetchOne(
            $connection->select()
                ->from($this->getMainTable(), 'data_version')
                ->limit(1)
        ) ?? false;
    }

    /**
     * @param string $version
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateDataVersion($version)
    {
        $updatedCount = $this->getConnection()->update($this->getMainTable(), ['data_version' => $version]);
        if (!$updatedCount) {
            $this->getConnection()->delete($this->getMainTable());
            $updatedCount = $this->getConnection()->insert($this->getMainTable(), ['data_version' => $version]);
        }
        return $updatedCount;
    }
}
