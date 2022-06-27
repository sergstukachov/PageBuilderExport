<?php

namespace SkillUp\PageBuilderExport\Model;

use Magento\Framework\Model\AbstractModel;

class DataVersion extends AbstractModel
{
    /**
     * Initialize DataVersion model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('SkillUp\PageBuilderExport\Model\ResourceModel\DataVersion');
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->_getResource()->loadDataVersion();
    }

    /**
     * @param string $version
     * @return int
     */
    public function updateVersion($version)
    {
        return $this->_getResource()->updateDataVersion($version);
    }
}
