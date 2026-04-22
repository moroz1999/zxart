<?php

class showParty extends structureElementAction
{
    /**
     * @param partyElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
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

