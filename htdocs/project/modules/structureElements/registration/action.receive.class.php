<?php

class receiveRegistration extends structureElementAction
{
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated === true) {
            $structureElement->prepareActualData();

            if ($structureElement->title != '') {
                $structureElement->structureName = $structureElement->title;
            }
            $structureElement->persistElementData();

            $linksManager = $this->getService('linksManager');
            if ($fieldsIds = $structureElement->getConnectedFieldsIds()) {
                foreach ($fieldsIds as &$fieldId) {
                    if (!in_array($fieldId, $structureElement->registrationFieldsIds)) {
                        $linksManager->unLinkElements($structureElement->id, $fieldId, registrationElement::FIELD_LINK_TYPE);
                    }
                }
            }
            foreach ($structureElement->registrationFieldsIds as $selectedCategoryId) {
                if (!$selectedCategoryId) {
                    continue;
                }
                $linksManager->linkElements($structureElement->id, $selectedCategoryId, registrationElement::FIELD_LINK_TYPE);
            }
            if ($groupsIds = $structureElement->getConnectedUserGroupsIds()) {
                foreach ($groupsIds as &$groupId) {
                    if (!in_array($groupId, $structureElement->registrationGroupsIds)) {
                        $linksManager->unLinkElements($structureElement->id, $groupId, registrationElement::USER_GROUP_LINK_TYPE);
                    }
                }
            }
            foreach ($structureElement->registrationGroupsIds as $selectedGroupId) {
                if (!$selectedGroupId) {
                    continue;
                }
                $linksManager->linkElements($structureElement->id, $selectedGroupId, registrationElement::USER_GROUP_LINK_TYPE);
            }
            $structureElement->persistDisplayMenusLinks();
            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->executeAction('showForm');
        }
    }

    public function setValidators(&$validators)
    {
        $validators['title'][] = 'notEmpty';
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'type',
            'content',
            'registrationFieldsIds',
            'registrationGroupsIds',
            'displayMenus',
        ];
    }
}