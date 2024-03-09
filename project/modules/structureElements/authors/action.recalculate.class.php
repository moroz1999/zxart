<?php

class recalculateAuthors extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($authors = $structureManager->getElementsByType('author', $structureElement->id)) {
            foreach ($authors as $author) {
                $author->executeAction('recalculate');
            }
        }
    }
}

