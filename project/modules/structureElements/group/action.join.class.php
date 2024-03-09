<?php

class joinGroup extends structureElementAction
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
             * @var GroupsManager $groupsManager
             */
            $groupsManager = $this->getService('GroupsManager');

            if ($structureElement->joinAsAlias) {
                $groupsManager->joinGroupAsAlias($structureElement->id, $structureElement->joinAsAlias);
            }
            if ($structureElement->joinAndDelete) {
                $groupsManager->joinDeleteGroup($structureElement->id, $structureElement->joinAndDelete);
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


