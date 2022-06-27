<?php

namespace SkillUp\PageBuilderExport\Controller\Adminhtml;

abstract class Generate extends \Magento\Backend\App\Action
{
    /**
     * Set generate result
     *
     * @param string $result
     * @return void
     */
    public function setGenerateResult($result)
    {
        if ($result->getScriptApplied()) {
            $this->messageManager->addSuccessMessage(
                __(
                    'Templates Script Generated and placed to template_setup folder. FileName is: %1',
                    $result->getScriptFileName()
                )
            );
        } else {
            $this->messageManager->addNotice(
                __(
                    'Templates Script Generated but can\'t be moved to templates_setup folder.
                     File is : %1',
                    $result->getScriptFileName()
                )
            );
        }
    }
}
