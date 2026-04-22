<?php

use ZxArt\Groups\Services\GroupsService;

class convertToGroupGroupAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param groupAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $groupsService = $this->getService(GroupsService::class);

            if ($newElement = $groupsService->convertGroupAliasToGroup($structureElement)) {
                $controller->redirect($newElement->getUrl());
            }
        }

        $structureElement->setViewName('form');
    }
}


