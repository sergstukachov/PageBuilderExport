<?php

namespace SkillUp\PageBuilderExport\Helper;

use SkillUp\PageBuilderExport\Model\Generator;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends AbstractHelper
{
    const UPGRADE_SCRIPT_SETUP_DIR = 'app/templates-upgrade-data';

    /**
     * Reader
     *
     * @var string
     */
    protected $_reader;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        Context $context,
        Reader $reader,
        DirectoryList $directoryList
    ) {
        $this->_reader = $reader;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_directoryList = $directoryList;

        parent::__construct($context);
    }

    /**
     * Make from string function name
     *
     * @static
     * @param string $name
     * @return string result
     */
    public function functionalize($name)
    {
        $name = explode(' ', str_replace(['_', '/', '-'], ' ', $name));
        $count = count($name);
        for ($i = 0; $i < $count; $i++) {
            $name[$i] = ucfirst($name[$i]);
        }
        $name = implode('', $name);

        return $name;
    }

    /**
     * @return mixed
     */
    public function getSetupDirConfigValue()
    {
        return self::UPGRADE_SCRIPT_SETUP_DIR;
    }

    /**
     * Get base path to module setup directory
     *
     * @return string
     */
    public function getModuleSetupDir()
    {
        $configPath = $this->getSetupDirConfigValue();
        return $this->_directoryList->getRoot() . DIRECTORY_SEPARATOR . trim($configPath, '/');
    }

    /**
     * Get base path to module setup data directory
     *
     * @return string
     */
    public function getModuleSetupDataDir()
    {
        return $this->getModuleSetupDir() . DIRECTORY_SEPARATOR;
    }

    /**
     * Get path to single upgrade script by script version
     *
     * @param string $version
     * @return string
     */
    public function getPathToUpgradeScript($version)
    {
        return $this->getModuleSetupDataDir() . Generator::UPGRADE_FILE_NAME . '-' . $version;
    }


    public function isModuleOutputEnabled($moduleName = null)
    {
//        if ($moduleName === null) {
//            $moduleName = $this->_getModuleName();
//        }
//
//        return !$this->_scopeConfig->isSetFlag(
//            'advanced/modules_disable_output/' . $moduleName,
//            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
//        );
        return true;
    }
}
