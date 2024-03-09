<?php

class recalculateAuthors extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($authors = $structureManager->getElementsByType('author', $structureElement->id)) {
            foreach ($authors as $author) {
                $author->executeAction('recalculate');
            }
        }
    }
}

