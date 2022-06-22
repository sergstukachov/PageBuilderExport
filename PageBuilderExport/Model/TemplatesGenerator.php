<?php

namespace SkillUp\PageBuilderExport\Model;

use Magento\PageBuilder\Api\TemplateRepositoryInterface;

class TemplatesGenerator extends Generator
{
    /** @var TemplateRepositoryInterface */
    protected $templateRepository;

    /**
     * PageGenerator constructor.
     *
     * @param GeneratorContext $context
     * @param TemplateRepositoryInterface $templateRepository
     */
    public function __construct(
        GeneratorContext $context,
        TemplateRepositoryInterface $templateRepository
    ) {
        $this->templateRepository = $templateRepository;

        parent::__construct($context);
    }

    /**
     * @param array $entityIds
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processUpgradeScript($entityIds)
    {
        $data = $this->_getUpgradeData();
        foreach ($entityIds as $templateId) {
            $template = $this->templateRepository->get($templateId);
            $this->_dispatchBeforeFillData($template);
            $data['items'][] = $this->_fillData($template);
        }
        $nextVersion = $this->_getNextModuleVersion();
        $put = $this->putUpgradeFile($data, $nextVersion);
        if ($put) {
            $this->_changeDbVersion($nextVersion);
        }

        return $this->result;
    }

}
