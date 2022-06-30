<?php

namespace SkillUp\PageBuilderExport\Model;

interface GeneratorInterface
{
    /**
     * Function generate
     *
     * @return mixed
     */
    public function generate();

    /**
     * Function get upgrade fields
     *
     * @return mixed
     */
    public function getUpgradeFields();

    /**
     * Function get entity type
     *
     * @return mixed
     */
    public function getEntityType();
}
