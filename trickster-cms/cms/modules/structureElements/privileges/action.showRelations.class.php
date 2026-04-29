<?php

class showRelationsPrivileges extends structureElementAction
{
    /**
     * @param privilegesElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $currentElement = $structureManager->getCurrentElement();

        if ($usersFolder = $structureManager->getElementByMarker('users')) {
            $structureElement->usersList = $structureManager->getElementsChildren($usersFolder->id);
        }

        if ($userGroupsFolder = $structureManager->getElementByMarker('userGroups')) {
            $structureElement->userGroupsList = $structureManager->getElementsChildren($userGroupsFolder->id);
        }

        $userId = false;
        if ($structureElement->userId != "") {
            $userId = $structureElement->userId;
        } elseif ($structureElement->userGroupId != "") {
            $userId = $structureElement->userGroupId;
        }
        if ($userId) {
            $privilegeRelationsCollection = persistableCollection::getInstance('privilege_relations');
            $searchFields = ['userId' => $userId, 'elementId' => $currentElement->id];
            $privilegesRelations = $privilegeRelationsCollection->load($searchFields);

            $compiledRelations = [];
            foreach ($privilegesRelations as $relation) {
                $compiledRelations[$relation->module][$relation->action] = $relation->getData();
            }

            $privilegesManager = $this->getService(privilegesManager::class);
            $structureElement->privileges = $privilegesManager->getModuleActionsList();

            foreach ($structureElement->privileges as $privilege) {
                if (isset($compiledRelations[$privilege->module]) && isset($compiledRelations[$privilege->module][$privilege->action])
                ) {
                    if ($compiledRelations[$privilege->module][$privilege->action]['type'] == '1') {
                        $privilege->type = 'allow';
                    } else {
                        $privilege->type = 'deny';
                    }
                } else {
                    $privilege->type = 'inherit';
                }
            }
        }
        $structureElement->setViewName('relations');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['userId', 'userGroupId'];
    }
}