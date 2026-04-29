<?php

class receiveRelationsPrivileges extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param privilegesElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $currentElement = $structureManager->getCurrentElement();

        $usersFolder = $structureManager->getElementByMarker('users');
        $structureElement->usersList = $structureManager->getElementsChildren($usersFolder->id);

        $userGroupsFolder = $structureManager->getElementByMarker('userGroups');
        $structureElement->userGroupsList = $structureManager->getElementsChildren($userGroupsFolder->id);

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
            foreach ($privilegesRelations as &$relation) {
                $compiledRelations[$relation->module][$relation->action] = $relation;
            }
            foreach ($structureElement->json as $key => $type) {
                $values = explode('/', $key);
                if (isset($values[0]) && isset($values[0])) {
                    $module = $values[0];
                    $action = $values[1];
                    if ($type == 'inherit') {
                        if (isset($compiledRelations[$module][$action])) {
                            $compiledRelations[$module][$action]->delete();
                        }
                    } else {
                        if (isset($compiledRelations[$module][$action])) {
                            $privilegesObject = $compiledRelations[$module][$action];
                        } else {
                            $collection = persistableCollection::getInstance('privilege_relations');
                            $privilegesObject = $collection->getEmptyObject();
                            $privilegesObject->elementId = $currentElement->id;
                            $privilegesObject->userId = $userId;
                            $privilegesObject->module = $module;
                            $privilegesObject->action = $action;
                        }

                        if ($type == 'allow') {
                            $privilegesObject->type = '1';
                        } else {
                            $privilegesObject->type = '0';
                        }
                        $privilegesObject->persist();
                    }
                }
            }
        }
        $structureElement->setViewName('relations');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'userId',
            'userGroupId',
            'json',
        ];
    }
}