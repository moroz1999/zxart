<?php

class convertToGroupAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            /**
             * @var GroupsManager $groupsManager
             */
            $groupsManager = $this->getService('GroupsManager');

            if ($newElement = $groupsManager->convertAuthorToGroup($structureElement)) {
                $controller->redirect($newElement->getUrl());
            }
        }

        $structureElement->setViewName('form');
    }
}


