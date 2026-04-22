<?php

class recalculateAuthors extends structureElementAction
{
    /**
     * @param authorsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($authors = $structureManager->getElementsByType('author', $structureElement->getId())) {
            foreach ($authors as $author) {
                $author->executeAction('recalculate');
            }
        }
    }
}

