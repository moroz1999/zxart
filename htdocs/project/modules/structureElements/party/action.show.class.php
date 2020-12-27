<?php

class showParty extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $view = $controller->getParameter('view');
            if ($view == 'hype' || $view == 'html') {
                $type = 'html';
                renderer::getInstance()->assign('textType', $type);
                $structureElement->setViewName('text');
            } elseif ($view == 'bb') {
                $type = 'bb';
                renderer::getInstance()->assign('textType', $type);
                $structureElement->setViewName('text');
            } elseif ($view == 'text') {
                $type = 'text';
                renderer::getInstance()->assign('textType', $type);
                $structureElement->setViewName('text');
            } else {
                $structureElement->setViewName('all');
            }
        }
    }
}

