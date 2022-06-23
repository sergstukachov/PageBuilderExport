<?php

namespace SkillUp\PageBuilderExport\Model;

use Magento\Framework\ObjectManager\ContextInterface;
use SkillUp\PageBuilderExport\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Event\ManagerInterface;
use SkillUp\PageBuilderExport\Model\DataVersion;

class GeneratorContext implements ContextInterface
{
    /**
     * @var \SkillUp\PageBuilderExport\Helper\Data
     */
    protected $helper = null;

    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    protected $logger = null;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $filesystemDriver = null;

    /**
     * @var null|\SkillUp\PageBuilderExport\Model\DataVersion
     */
    protected $dataVersion = null;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * GeneratorContext constructor.
     *
     * @param \SkillUp\PageBuilderExport\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem\Driver\File $filesystemDriver
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \SkillUp\PageBuilderExport\Model\DataVersion $dataVersion
     */
    public function __construct(
        Data $helper,
        LoggerInterface $logger,
        File $filesystemDriver,
        ManagerInterface $eventManager,
        DataVersion $dataVersion
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->filesystemDriver = $filesystemDriver;
        $this->eventManager = $eventManager;
        $this->dataVersion = $dataVersion;
    }

    /**
     * @return \SkillUp\PageBuilderExport\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return File
     */
    public function getFileSystemDriver()
    {
        return $this->filesystemDriver;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @return DataVersion
     */
    public function getDataVersion()
    {
        return $this->dataVersion;
    }
}
