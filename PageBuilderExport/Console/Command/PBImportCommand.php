<?php

namespace SkillUp\PageBuilderExport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Psr\Log\LoggerInterface;
use SkillUp\PageBuilderExport\Helper\Data;
use SkillUp\PageBuilderExport\Model\DataVersion;
use SkillUp\PageBuilderExport\Console\Command\Import\Save;

class PBImportCommand extends Command
{
    /**
     * Version argument
     */
    public const VERSION_ARGUMENT = 'version';

    /** @var \SkillUp\PageBuilderExport\Helper\Data */
    protected Data $helper;

    /** @var \Magento\Framework\Filesystem\Driver\File */
    protected $filesystemDriver;

    /** @var null|\SkillUp\PageBuilderExport\Model\DataVersion */
    protected $dataVersion = null;

    /** @var null|\Psr\Log\LoggerInterface */
    protected $logger = null;

    /**
     * @var null|\Magento\Framework\App\State
     */
    protected $appState = null;

    /**
     * @var \SkillUp\PageBuilderExport\Console\Command\Import\Save
     */
    protected $import;

    /**
     * @param Save $import
     * @param File $filesystemDriver
     * @param Data $helper
     * @param DataVersion $dataVersion
     * @param LoggerInterface $logger
     * @param State $appState
     */
    public function __construct(
        Save $import,
        File $filesystemDriver,
        Data $helper,
        DataVersion $dataVersion,
        LoggerInterface $logger,
        State $appState
    ) {
        $this->import = $import;
        $this->filesystemDriver = $filesystemDriver;
        $this->helper = $helper;
        $this->dataVersion = $dataVersion;
        $this->logger = $logger;
        $this->appState = $appState;
        parent::__construct();
    }

    /**
     * Command for import templates
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('skillup:templates_import')
            ->setDescription('Import PB Data.')->setDefinition(
                [
                    new InputArgument(
                        self::VERSION_ARGUMENT,
                        InputArgument::OPTIONAL,
                        'Version of import script for update'
                    ),
                ]
            );
    }

    /**
     * Console command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->emulateAreaCode(
            Area::AREA_ADMINHTML,
            [$this, 'executeImportCommand'],
            [$input, $output]
        );
    }

    /**
     * Console command import
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function executeImportCommand(InputInterface $input, OutputInterface $output)
    {
        try {
            if ($input->getArgument('version')) {
                $this->upgradeVersion($input->getArgument('version'), $output);
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage() . ': ' . 'Step skipped');
        }
    }

    /**
     * Upgrade Version
     *
     * @param string $version
     * @param OutputInterface $output
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function upgradeVersion($version, $output)
    {
        if ($this->filesystemDriver->isExists($this->helper->getPathToUpgradeScript($version))) {
            if ($this->applyImportScript($this->helper->getPathToUpgradeScript($version))) {
                $output->writeln('Import script with version ' . $version . ' has been successfully applied');
            } else {
                $output->writeln('Cannot apply import script');
            }
        } else {
            $output->writeln('Import script file not found: ' . $this->helper->getPathToUpgradeScript($version));
        }
    }

    /**
     * Apply Import Script
     *
     * @param string $file
     * @param bool $checkUpdateDate
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function applyImportScript(string $file, bool $checkUpdateDate = false): bool
    {
        $data = $this->filesystemDriver->fileGetContents($file);
        $data = json_decode($data, true);

        if ($this->import) {
            $this->import->setUpgradeData($data['items']);
            $this->import->upgrade($checkUpdateDate);
            return true;
        }

        return false;
    }
}
