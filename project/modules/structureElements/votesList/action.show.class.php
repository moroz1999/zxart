<?php

class showVotesList extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $type = 'popular';
        if ($structureElement->type) {
            $type = $structureElement->type;
        }
        $structureElement->setViewName($type);
    }
}

