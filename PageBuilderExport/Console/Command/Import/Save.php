<?php

declare(strict_types=1);

namespace SkillUp\PageBuilderExport\Console\Command\Import;

use Magento\Framework\Exception\LocalizedException;
use Magento\PageBuilder\Model\TemplateRepository;
use Magento\PageBuilder\Model\TemplateFactory;
use Psr\Log\LoggerInterface;

class Save
{
    public const CHECK_UPDATE_DATE_FLAG = '_check_update_date';

    /**
     * @var string
     */
    protected $fieldUpdateTime = 'update_time';

    /**
     * @var array
     */
    protected $upgradeData = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var TemplateRepository
     */
    protected $templateRepository;

    /**
     * Construct
     *
     * @param TemplateFactory $templateFactory
     * @param TemplateRepository $templateRepository
     */
    public function __construct(
        TemplateFactory $templateFactory,
        TemplateRepository $templateRepository
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateRepository = $templateRepository;
    }

    /**
     * Upgrade data
     *
     * @param bool $checkUpdateDate
     * @return void
     */
    public function upgrade($checkUpdateDate = false)
    {
        foreach ($this->upgradeData as $data) {
            if ($checkUpdateDate) {
                if (!isset($data[$this->fieldUpdateTime])) {
                    continue;
                }
                $data[self::CHECK_UPDATE_DATE_FLAG] = $checkUpdateDate;
            }

            $this->prepareModel($data);
        }
    }

    /**
     * Set upgrade data
     *
     * @param array $data
     * @return $this
     */
    public function setUpgradeData(array $data)
    {
        $this->upgradeData = $data;
        return $this;
    }

    /**
     * Seve data
     *
     * @param array $data
     * @return array|string[]
     */
    public function prepareModel(array $data)
    {
        $template = $this->templateFactory->create();
        $template->setPreviewImage($data['preview_image']);
        $template->setName($data['name']);
        $template->setTemplate($data['template']);
        $template->setCreatedFor($data['created_for']);
        $template->setCreatedAt($data['created_at']);
        $template->setUpdatedAt($data['updated_at']);
        try {
            $this->templateRepository->save($template);
            $result = [
                'status' => 'ok',
                'message' => __('Template was successfully saved.'),
                'data' => $template->toArray()
            ];
        } catch (LocalizedException $e) {
            $result = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);

            $result = [
              'status' => 'error'
            ];
        }
        return $result;
    }
}
