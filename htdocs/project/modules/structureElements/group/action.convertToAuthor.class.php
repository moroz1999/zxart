<?php

class convertToAuthorGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param groupElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            /**
             * @var AuthorsManager $authorsManager
             */
            $authorsManager = $this->getService('AuthorsManager');

            if ($newElement = $authorsManager->convertGroupToAuthor($structureElement)) {
                $controller->redirect($newElement->getUrl());
            }
        }

        $structureElement->setViewName('form');
    }
}


