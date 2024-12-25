<?php

use ZxArt\Groups\Services\GroupsService;

class convertToGroupAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            /**
             * @var GroupsService $groupsService
             */
            $groupsService = $this->getService(GroupsService::class);

            if ($newElement = $groupsService->convertAuthorToGroup($structureElement)) {
                $controller->redirect($newElement->getUrl());
            }
        }

        $structureElement->setViewName('form');
    }
}


