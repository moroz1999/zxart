<?php

class showSearch extends structureElementAction
{
    /**
     * @param searchElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('form');

        $renderer = $this->getService(renderer::class);
        $renderer->assign('searchFormElement', $structureElement);

        $structureElement->setViewName('result');
    }
}

