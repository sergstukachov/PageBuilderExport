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
    const VERSION_ARGUMENT = 'version';

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

     function __construct(
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
     * @param InputInterface $input
     * @param OutputInterface $output
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function executeImportCommand(InputInterface $input, OutputInterface $output)
    {
        try {
            if ($input->getArgument('version')) {
                $this->upgradeVersion($input->getArgument('version'), $output);
            } else {
                $this->importAll($output);
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage() . ': ' . 'Step skipped');
        }
    }

    /**
     * @param $version
     * @param $output
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function upgradeVersion($version, $output): void
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
     * @param $output
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function importAll($output): void
    {
        if ($this->filesystemDriver->isExists($this->helper->getModuleSetupDataDir())) {
            $dir = $this->filesystemDriver->readDirectory($this->helper->getModuleSetupDataDir());
            try {
                $lastUpdatedVersion = null;
                foreach ($dir as $file) {
                    $fileName = basename($file);
                    $fileNamesArr = explode('-', $file);
                    $fileVersion = end($fileNamesArr);
                    $isFileInstalled = $this->dataVersion->isFileInstalled($fileName);

                    if (version_compare($this->dataVersion->getVersion(), $fileVersion) < 0) {
                        if ($this->applyImportScript($file)) {
                            $output->writeln('Imported version ' . $fileVersion);
                            $lastUpdatedVersion = $fileVersion;
                        }
                        if (!$isFileInstalled) {
                            $this->dataVersion->installFile($fileName);
                        }
                    } else {
                        if (!$isFileInstalled) {
                            $this->applyImportScript($file, true);
                            $this->dataVersion->installFile($fileName);
                            $output->writeln('Installed old version ' . $fileVersion);
                        }
                    }
                }
                if ($lastUpdatedVersion !== null) {
                    $this->dataVersion->updateVersion($lastUpdatedVersion);
                    $output->writeln('DB data version is updated to version: ' . $lastUpdatedVersion);
                } else {
                    $output->writeln('No data for import');
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $output->writeln('Some error is occurred: ' . $e->getMessage());
            }
        } else {
            $output->writeln('Import files not found');
        }
    }

    /**
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
