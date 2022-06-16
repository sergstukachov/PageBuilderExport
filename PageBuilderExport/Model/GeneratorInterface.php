<?php

namespace SkillUp\PageBuilderExport\Model;

interface GeneratorInterface
{
    /**
     * @return mixed
     */
    public function generate();

    /**
     * @return mixed
     */
    public function getUpgradeFields();

    /**
     * @return mixed
     */
    public function getEntityType();
}
