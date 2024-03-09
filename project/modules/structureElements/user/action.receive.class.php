<?php

class receiveUser extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param userElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $userGroupsFolder = $structureManager->getElementByMarker('userGroups');
            $userGroups = $structureManager->getElementsChildren($userGroupsFolder->id);
            $structureElement->userGroupsList = [];
            foreach ($userGroups as $group) {
                $userGroup = clone($group);
                $userGroup->linkExists = false;
                $structureElement->userGroupsList[] = $userGroup;
            }

            $structureElement->structureName = $structureElement->userName;
            $structureElement->persistElementData();

            if (count($structureElement->userGroups) > 0) {
                $userGroupIdList = array_flip($structureElement->userGroups);
                foreach ($structureElement->userGroupsList as $userGroup) {
                    if (isset($userGroupIdList[$userGroup->id])) {
                        $userGroup->linkExists = true;
                    }
                }
            }

            $linksCollection = persistableCollection::getInstance('structure_links');
            $searchFields = ['childStructureId' => $structureElement->id, 'type' => 'userRelation'];
            $userLinks = $linksCollection->load($searchFields);

            $compiledUserLinks = [];
            foreach ($userLinks as $userLink) {
                $groupId = $userLink->parentStructureId;
                $compiledUserLinks[$groupId] = $userLink;
            }
            $collection = persistableCollection::getInstance('structure_links');
            foreach ($structureElement->userGroupsList as $userGroup) {
                if (isset($compiledUserLinks[$userGroup->id]) && $userGroup->linkExists != true) {
                    $compiledUserLinks[$userGroup->id]->delete();
                } elseif (!isset($compiledUserLinks[$userGroup->id]) && $userGroup->linkExists == true) {
                    //todo: use linksManager method instead.
                    $linksObject = $collection->getEmptyObject();
                    $linksObject->childStructureId = $structureElement->id;
                    $linksObject->parentStructureId = $userGroup->id;
                    $linksObject->type = 'userRelation';
                    $linksObject->persist();
                }
            }

            $structureElement->checkSubscription($structureElement->subscribe);

            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->executeAction("showForm");
        }
    }

    public function setValidators(&$validators): void
    {
        $validators['userName'][] = 'notEmpty';
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'company',
            'firstName',
            'lastName',
            'address',
            'city',
            'postIndex',
            'country',
            'email',
            'phone',
            'subscribe',
            'userName',
            'password',
            'website',
            'showemail',
            'userGroups',
        ];
    }
}


