<?php

class showFormLogin extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->setViewName('show');
        $renderer = $this->getService('renderer');
        $renderer->assign('loginForm', $structureElement);

        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService('renderer');
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
            $renderer->assign('form', $structureElement->getForm('form'));
        }
    }
}