<?php

class showCommentsList extends structureElementAction
{
    /**
     * @param commentsListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $type = 'popular';
        if ($structureElement->type) {
            $type = $structureElement->type;
        }
        $structureElement->setViewName($type);
    }
}

