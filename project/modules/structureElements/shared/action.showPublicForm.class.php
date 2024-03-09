<?php

class showPublicFormShared extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
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

