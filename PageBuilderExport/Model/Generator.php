<?php

namespace SkillUp\PageBuilderExport\Model;

use Magento\Framework\DataObject;
use Symfony\Component\Config\Definition\Exception\Exception;


class Generator
{
    const UPGRADE_FILE_NAME = 'data-upgrade';

    /** @var DataObject */
    protected $_result;

    /** @var \SkillUp\PageBuilderExport\Helper\Data */
    protected $_helper = null;

    /** @var null|\Psr\Log\LoggerInterface */
    protected $_logger = null;

    /** @var GeneratorInterface */
    protected $_generateEntity = null;

    /** @var \Magento\Framework\Filesystem\Driver\File */
    protected $_filesystemDriver = null;

    /** @var null|\SkillUp\PageBuilderExport\Model\DataVersion */
    protected $_dataVersion = null;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Generator constructor.
     *
     * @param GeneratorContext $context
     */
    public function __construct(
        GeneratorContext $context
    ) {
        $this->_result = new DataObject();
        $this->_result->setError(false);
        $this->_helper = $context->getHelper();
        $this->_logger = $context->getLogger();
        $this->_filesystemDriver = $context->getFileSystemDriver();
        $this->_eventManager = $context->getEventManager();
        $this->_dataVersion = $context->getDataVersion();
        $this->_checkUpgradeDataFolder();
    }

    /**
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _checkUpgradeDataFolder()
    {
        if (empty($this->_helper->getSetupDirConfigValue())) {
            self::throwException(
                __(
                    'Upgrade script directory can not be created.
                    Please set Upgrade Script Directory in System Configuration'
                )
            );
        }

        $path = $this->_helper->getModuleSetupDataDir();
        if (!$this->_filesystemDriver->isExists($path)) {
            $this->_filesystemDriver->createDirectory($path, 0775);
        }

        if (!$this->_filesystemDriver->isWritable($path)) {
            self::throwException(
                __('Upgrade script directory : %1 can not be created. Please check permissions', $path)
            );
        }
    }

    /**
     * @param GeneratorInterface $generateEntity
     * @return $this
     */
    public function setGenerateEntity(GeneratorInterface $generateEntity)
    {
        $this->_generateEntity = $generateEntity;
        return $this;
    }

    /**
     * Update data base data version
     *
     * @param string $nextVersion
     * @return mixed
     */
    protected function _changeDbVersion($nextVersion)
    {
        try {
            $fileName = self::UPGRADE_FILE_NAME . '-' . $this->_getCurrentVersion() . '-' . $nextVersion;
            $result = $this->_dataVersion->updateVersion($nextVersion, $fileName);
            if ($result) {
                $this->_result->setConfigVersionApplied(true);
                return true;
            }
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        $this->_result->setConfigVersionApplied(false);
        return false;
    }

    /**
     * @param string $content
     * @param string $nextVersion
     * @return bool
     */
    protected function putUpgradeFile($content, $nextVersion)
    {
        $version = $this->_getCurrentVersion() . '-' . $nextVersion;
        $fullFileName = $this->_helper->getPathToUpgradeScript($version);
        $this->_result->setScriptFileName($fullFileName);
        $this->_result->setScriptApplied(false);
        $this->_result->setNextConfigVersion($nextVersion);

        try {
            $this->_filesystemDriver->filePutContents($fullFileName, json_encode($content, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        if ($this->_filesystemDriver->isExists($fullFileName)) {
            $this->_result->setScriptApplied(true);
            return true;
        } else {
            $this->_result->setErrorMsg(__('File : %1 can not be created. Please check permissions', $fullFileName));
        }
        return false;
    }

    /**
     * Fill data from entity
     *
     * @param object $entity
     * @return array
     */
    protected function _fillData($entity)
    {
        $this->_dispatchBeforeFillData($entity);

        $result = [];
        foreach ($this->_generateEntity->getUpgradeFields() as $field) {
            $fieldGetter = 'get' . $this->_helper->functionalize($field);
            $result[$field] = $entity->{$fieldGetter}();
        }
        return $result;
    }

    /**
     * Dispatch event before fill data
     *
     * @param object $entity
     * @return void
     */
    protected function _dispatchBeforeFillData($entity)
    {
        $this->_eventManager->dispatch(
            'before_upgrade_script_fill_data_' . $this->_generateEntity->getEntityType(),
            [
                'generate_entity' => $this->_generateEntity,
                'entity' => $entity
            ]
        );
    }

    /**
     * @return string
     */
    protected function _getCurrentVersion()
    {
        return (string)$this->_dataVersion->getVersion();
    }

    /**
     * @return string
     */
    protected function _getNextModuleVersion()
    {
        return $this->calculateNextVersion($this->_getCurrentVersion());
    }

    /**
     * @return array
     */
    protected function _getUpgradeData()
    {
        return ['entityType' => $this->_generateEntity->getEntityType(), 'items' => []];
    }

    /**
     * @param string $basic
     * @return string
     */
    public function calculateNextVersion($basic)
    {
        $exploded = explode('.', $basic);

        $c = count($exploded);
        for ($i = $c - 1; $i >= 0; $i--) {
            if (($exploded[$i] < 9) && ($i == $c - 1)) {
                $exploded[$i] = $exploded[$i] + 1;
                break;
            } elseif ($exploded[$i] == 9) {
                $exploded[$i] = 0;
                if (!isset($exploded[$i - 1])) {
                    array_unshift($exploded, 1);
                } else {
                    $exploded[$i - 1] = $exploded[$i - 1] + 1;
                }
            } elseif ($exploded[$i] > 9) {
                $exploded[$i] = $exploded[$i] % 10;
                if (!isset($exploded[$i - 1])) {
                    array_unshift($exploded, 1);
                }
            }
        }
        $new = implode('.', $exploded);

        return $new;
    }

    /**
     * @param string $msg
     * @return void
     * @throws \Exception
     */
    public static function throwException($msg)
    {
        throw new \Exception($msg);
    }
}
