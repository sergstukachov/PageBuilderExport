<?php

namespace SkillUp\PageBuilderExport\Model\Entity;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use SkillUp\PageBuilderExport\Model\Templates\AbstractEntity as TemplatesAbstractEntity;

class AbstractEntity extends TemplatesAbstractEntity
{
    /**
     * @var array
     */
    protected $itemsIds = [];

    /**
     * @param array $ids
     * @return $this
     */
    public function setItemsIds(array $ids)
    {
        $this->itemsIds = $ids;
        return $this;
    }

    /**
     * @throws LocalizedException
     */
    public function generate(): DataObject
    {
        return $this->_generator->processUpgradeScript($this->itemsIds);
    }
}
