<?php

namespace SkillUp\PageBuilderExport\Model;

use Magento\Framework\DataObject;
use Symfony\Component\Config\Definition\Exception\Exception;

class Generator
{
    public const UPGRADE_FILE_NAME = 'data-upgrade';

    /** @var DataObject */
    protected $result;

    /** @var \SkillUp\PageBuilderExport\Helper\Data */
    protected $helper = null;

    /** @var null|\Psr\Log\LoggerInterface */
    protected $logger = null;

    /** @var GeneratorInterface */
    protected $generateEntity = null;

    /** @var \Magento\Framework\Filesystem\Driver\File */
    protected $filesystemDriver = null;

    /** @var null|\SkillUp\PageBuilderExport\Model\DataVersion */
    protected $dataVersion = null;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * Generator constructor.
     *
     * @param GeneratorContext $context
     */
    public function __construct(
        GeneratorContext $context
    ) {
        $this->result = new DataObject();
        $this->result->setError(false);
        $this->helper = $context->getHelper();
        $this->logger = $context->getLogger();
        $this->filesystemDriver = $context->getFileSystemDriver();
        $this->eventManager = $context->getEventManager();
        $this->dataVersion = $context->getDataVersion();
        $this->messageManager = $context->getMessageManager();
        $this->_checkUpgradeDataFolder();
    }

    /**
     * Function check upgrade data folder
     *
     * @return void
     * @throws \Exception
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _checkUpgradeDataFolder()
    {
        if (!$this->filesystemDriver->isExists($this->helper->getModuleSetupDataDir())) {
            //Create directory
            try {
                $this->filesystemDriver->createDirectory($this->helper->getModuleSetupDataDir());
            } catch (\Throwable $exception) {
                return;
            }
        }
        if (empty($this->helper->getSetupDirConfigValue())) {
                $this->messageManager->addErrorMessage(__(
                    'Upgrade script directory can not be created.
                    Please set Upgrade Script Directory in System Configuration'
                ));
        }

        $path = $this->helper->getModuleSetupDataDir();
        if (!$this->filesystemDriver->isExists($path)) {
            $this->filesystemDriver->createDirectory($path, 0775);
        }

        if (!$this->filesystemDriver->isWritable($path)) {
            $this->messageManager->addErrorMessage(
                __('Upgrade script directory : %1 can not be created. Please check permissions' . $path)
            );
        }
    }

    /**
     * Function set generate entity
     *
     * @param GeneratorInterface $generateEntity
     * @return $this
     */
    public function setGenerateEntity(GeneratorInterface $generateEntity)
    {
        $this->generateEntity = $generateEntity;
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
            $result = $this->dataVersion->updateVersion($nextVersion, $fileName);
            if ($result) {
                $this->result->setConfigVersionApplied(true);
                return true;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->result->setConfigVersionApplied(false);
        return false;
    }

    /**
     * Function upgrade file
     *
     * @param string $content
     * @param string $nextVersion
     *
     * @return bool
     */
    protected function putUpgradeFile($content, $nextVersion)
    {
        $version = $this->_getCurrentVersion() . '-' . $nextVersion;
        $fullFileName = $this->helper->getPathToUpgradeScript($version);
        $this->result->setScriptFileName($fullFileName);
        $this->result->setScriptApplied(false);
        $this->result->setNextConfigVersion($nextVersion);

        try {
            $this->filesystemDriver->filePutContents($fullFileName, json_encode($content, JSON_PRETTY_PRINT));
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        if ($this->filesystemDriver->isExists($fullFileName)) {
            $this->result->setScriptApplied(true);
            return true;
        } else {
            $this->result->setErrorMsg(__('File : %1 can not be created. Please check permissions', $fullFileName));
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
        foreach ($this->generateEntity->getUpgradeFields() as $field) {
            $fieldGetter = 'get' . $this->helper->functionalize($field);
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
        $this->eventManager->dispatch(
            'before_upgrade_script_fill_data_' . $this->generateEntity->getEntityType(),
            [
                'generate_entity' => $this->generateEntity,
                'entity' => $entity
            ]
        );
    }

    /**
     * Function Get Current Version
     *
     * @return string
     */
    protected function _getCurrentVersion()
    {
        if ($this->dataVersion->getVersion() == '') {
            return '0.0.1';
        }
        return $this->dataVersion->getVersion();
    }

    /**
     * Function Get Next Module Version
     *
     * @return string
     */
    protected function _getNextModuleVersion()
    {
        return $this->calculateNextVersion($this->_getCurrentVersion());
    }

    /**
     * Function Get Upgrade Data
     *
     * @return array
     */
    protected function _getUpgradeData()
    {
        return ['entityType' => $this->generateEntity->getEntityType(), 'items' => []];
    }

    /**
     * Function Calculate Next Version
     *
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
        return implode('.', $exploded);
    }
}
