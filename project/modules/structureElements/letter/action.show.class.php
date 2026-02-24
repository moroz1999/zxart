<?php

class showLetter extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->requested) {
            $renderer = $this->getService(renderer::class);
            $renderer->assign('lettersInfo', $structureElement->getLettersInfo());
        }
    }
}

