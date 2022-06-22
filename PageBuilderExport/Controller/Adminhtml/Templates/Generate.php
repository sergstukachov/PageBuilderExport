<?php

namespace SkillUp\PageBuilderExport\Controller\Adminhtml\Templates;

use Magento\PageBuilder\Model\ResourceModel\Template\CollectionFactory;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use SkillUp\PageBuilderExport\Model\Entity\AbstractEntity;
use SkillUp\PageBuilderExport\Controller\Adminhtml\Generate as SkGenerate;

class Generate extends SkGenerate
{

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var string
     */
    protected $entityLabel = '';

    /**
     * @var AbstractEntity
     */
    protected $templates;

    /**
     * Generate constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param AbstractEntity $templates
     * @param $entityLabel
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        AbstractEntity $templates,
        $entityLabel
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->templates = $templates;
        $this->entityLabel = $entityLabel;

        parent::__construct($context);
    }

    /**
     * @return $this|bool
     */
    public function execute()
    {
        $ids = $this->getTemplatesIds();
        if (!is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select a ' . $this->entityLabel . '(s).'));
        } else {
            $this->templates->setItemsIds($ids);
            try {
                $result = $this->templates->generate();
                $this->setGenerateResult($result);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setRefererOrBaseUrl();
    }


    protected function getTemplatesIds()
    {
        $ids = $this->getRequest()->getParam(Filter::SELECTED_PARAM) ?? null;
        if (!$ids && 'false' === $this->getRequest()->getParam(Filter::EXCLUDED_PARAM)) {
            $collection = $this->collectionFactory->create();
            $ids = $collection->getAllIds();
        }

        return $ids;
    }
}
