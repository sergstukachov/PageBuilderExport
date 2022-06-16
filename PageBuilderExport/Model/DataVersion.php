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
     * @param string $fileName
     * @return int
     */
    public function updateVersion($version, $fileName = null)
    {
        return $this->_getResource()->updateDataVersion($version, $fileName);
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public function installFile($fileName)
    {
        return $this->_getResource()->installFile($fileName);
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public function isFileInstalled($fileName)
    {
        return $this->_getResource()->isFileInstalled($fileName);
    }
}
