<?php

class showFormZxMusic extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', false);
            $renderer->assign('contentSubTemplate', 'zxMusic.form.tpl');
        }
        if ($structureElement->requested) {
            if ($structureElement->tagsText == '') {
                $structureElement->tagsText = $structureElement->generateTagsText();
            }
        }
    }
}

