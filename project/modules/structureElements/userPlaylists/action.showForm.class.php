<?php

class showFormUserPlaylists extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService('renderer');
            $renderer->assign('tabsTemplate', 'shared.tabs.tpl');
            $renderer->assign('contentSubTemplate', 'userPlaylists.form.tpl');
        }
    }
}


