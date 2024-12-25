<?php

use ZxArt\Groups\Services\GroupsService;

class joinGroupAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param groupElement $structureElement
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

            if ($structureElement->joinAndDelete) {
                $groupsService->joinDeleteGroup($structureElement->id, $structureElement->joinAndDelete);
            }
            $controller->redirect($structureElement->getUrl());
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'joinAndDelete',
        ];
    }
}


