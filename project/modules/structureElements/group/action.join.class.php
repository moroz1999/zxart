<?php

use ZxArt\Groups\Services\GroupsService;

class joinGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param groupElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $groupsService = $this->getService(GroupsService::class);

            if ($structureElement->joinAsAlias) {
                $groupsService->joinGroupAsAlias($structureElement->getId(), $structureElement->joinAsAlias);
            }
            if ($structureElement->joinAndDelete) {
                $groupsService->joinDeleteGroup($structureElement->getId(), $structureElement->joinAndDelete);
            }

            $controller->redirect($structureElement->getUrl());
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'joinAsAlias',
            'joinAndDelete',
        ];
    }
}


