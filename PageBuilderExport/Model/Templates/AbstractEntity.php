<?php

namespace SkillUp\PageBuilderExport\Model\Templates;

use SkillUp\PageBuilderExport\Model\GeneratorInterface;
use Magento\PageBuilder\Model\ResourceModel\Template\Collection as AbstractCollection;
use SkillUp\PageBuilderExport\Model\Generator;
use SkillUp\PageBuilderExport\Model\TemplatesGenerator;


class AbstractEntity implements GeneratorInterface
{

    /** @var TemplatesGenerator */
    protected $generator = null;

    /** @var AbstractCollection */
    protected $_collection = null;

    /**
     * @var array
     */
    protected $_upgradeFields = [];

    /**
     * @var string
     */
    protected $_entityType = '';

    /**
     * AbstractEntity constructor.
     *
     * @param Generator $generator
     * @param string $entityType
     * @param array $upgradeFields
     */
    public function __construct(
        Generator $generator,
        $entityType,
        array $upgradeFields = []
    ) {
        $this->_entityType = $entityType;
        $this->_upgradeFields = $upgradeFields;

        $this->_generator = $generator;
        $this->_generator->setGenerateEntity($this);
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->_entityType;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function generate()
    {
        return $this->_generator->processUpgradeScript($this->_collection);
    }

    /**
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    public function setCollection(AbstractCollection $collection): AbstractCollection
    {
        $this->_collection = $collection;

        return $collection;
    }

    /**
     * @param string|array $fields
     * @return $this
     */
    public function addUpgradeFields($fields)
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $this->_upgradeFields = array_merge($this->_upgradeFields, $fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getUpgradeFields()
    {
        return $this->_upgradeFields;
    }
}
