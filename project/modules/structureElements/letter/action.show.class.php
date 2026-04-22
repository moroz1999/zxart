<?php

class showLetter extends structureElementAction
{
    /**
     * @param letterElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested) {
            $renderer = $this->getService(renderer::class);
            $renderer->assign('lettersInfo', $structureElement->getLettersInfo());
        }
    }
}

