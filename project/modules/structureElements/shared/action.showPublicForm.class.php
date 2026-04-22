<?php

class showPublicFormShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setViewName('form');
        }
        if ($structureElement->requested) {
            if ($structureElement->tagsText == '') {
                $structureElement->tagsText = $structureElement->generateTagsText();
            }
        }
    }
}

