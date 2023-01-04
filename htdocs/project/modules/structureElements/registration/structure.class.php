<?php

class registrationElement extends menuDependantStructureElement
{
    const FIELD_LINK_TYPE = 'registrationField';
    const USER_GROUP_LINK_TYPE = 'registrationUserGroup';
    public $dataResourceName = 'module_registration';
    public $defaultActionName = 'show';
    public $role = 'content';
    public $socialConnectionSuccess = false;
    protected $connectedFields;
    protected $connectedFieldsIndex;
    protected $dynamicFieldsErrors = [];

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';

        $moduleStructure['company'] = 'text';
        $moduleStructure['firstName'] = 'text';
        $moduleStructure['lastName'] = 'text';
        $moduleStructure['address'] = 'text';
        $moduleStructure['city'] = 'text';
        $moduleStructure['postIndex'] = 'text';
        $moduleStructure['country'] = 'text';
        $moduleStructure['email'] = 'email';
        $moduleStructure['phone'] = 'text';
        $moduleStructure['website'] = 'text';

        $moduleStructure['type'] = 'text';
        $moduleStructure['subscribe'] = 'checkbox';
        $moduleStructure['needPresentation'] = 'checkbox';
        $moduleStructure['content'] = 'html';
        $moduleStructure['password'] = 'text';
        $moduleStructure['passwordRepeat'] = 'text';
        $moduleStructure['registrationFieldsIds'] = 'numbersArray';
        $moduleStructure['registrationGroupsIds'] = 'numbersArray';
        $moduleStructure['dynamicFieldsData'] = 'array';
    }

    public function getSocialPluginsOptions()
    {
        $result = [];
        $existingConnections = [];
        $user = $this->getService('user');
        $registrationMode = $this->type == 'registration';
        $currentUserId = $user->userName !== 'anonymous' ? (int)$user->readUserId() : 0;

        if ($currentUserId > 0) {
            $socialDataManager = $this->getService('SocialDataManager');
            $existingConnections = $socialDataManager->getCmsUserSocialNetworks($currentUserId);
        }
        $controller = controller::getInstance();
        $socialDataManager = $this->getService('SocialDataManager');
        $socialPlugins = $socialDataManager->getSocialPlugins();
        foreach ($socialPlugins as $element) {
            $iconUrl = '';
            if ($element->icon) {
                $iconUrl = $controller->baseURL . 'image/type:registrationSocialPluginIcon/id:'
                    . $element->icon . '/filename:' . $element->iconOriginalName;
            }
            $connected = in_array($element->getName(), $existingConnections);
            $action = 'login';
            if (!$registrationMode) {
                $action = $connected ? 'disconnect' : 'connect';
            }
            $url = $element->getSocialActionUrl($action);
            $result[] = [
                'title' => $element->title,
                'code' => $element->getName(),
                'url' => $url,
                'connected' => $connected,
                'icon' => $iconUrl,
            ];
        }
        return $result;
    }

    //TODO: investigate. Is it used anywhere?
    public function getFieldOptions()
    {
        $result = [];
        $structureManager = $this->getService('structureManager');
        if ($fieldsElement = $structureManager->getElementByMarker('registrationFields')) {
            $result = $structureManager->getElementsChildren($fieldsElement->id);
        }
        return $result;
    }

    public function getConnectedFields()
    {
        if ($this->connectedFields === null) {
            $this->connectedFields = [];
            $this->connectedFieldsIndex = [];
            if ($fieldsIds = $this->getConnectedFieldsIds()) {
                $structureManager = $this->getService('structureManager');
                foreach ($fieldsIds as &$fieldId) {
                    if ($fieldId && $element = $structureManager->getElementById($fieldId)) {
                        $this->connectedFieldsIndex[$fieldId] = $element;
                        $this->connectedFields[] = $element;
                    }
                }
            }
        }
        return $this->connectedFields;
    }

    public function getConnectedFieldById($id)
    {
        if ($this->connectedFieldsIndex === null) {
            $this->getConnectedFields();
        }
        return isset($this->connectedFieldsIndex[$id]) ? $this->connectedFieldsIndex[$id] : null;
    }

    public function getConnectedFieldsIds()
    {
        return $this->getService('linksManager')->getConnectedIdList($this->id, self::FIELD_LINK_TYPE, 'parent');
    }

    public function getConnectedUserGroupsIds()
    {
        return $this->getService('linksManager')->getConnectedIdList($this->id, self::USER_GROUP_LINK_TYPE, 'parent');
    }

    public function setDynamicFieldError($fieldId)
    {
        $this->dynamicFieldsErrors[$fieldId] = true;
    }

    public function getDynamicFieldError($fieldId)
    {
        return isset($this->dynamicFieldsErrors[$fieldId]);
    }

    public function getDynamicFieldErrors()
    {
        return $this->dynamicFieldsErrors;
    }

    public function getFieldValue($fieldId)
    {
        $value = '';
        $actionParameter = controller::getInstance()->getParameter('action');
        $formData = $this->getFormData();
        if ($actionParameter == 'submit') {
            $formData = $this->getFormData();
            if (isset($formData['dynamicFieldsData'][$fieldId])) {
                $value = $formData['dynamicFieldsData'][$fieldId];
            }
        } elseif ($this->type == 'registration') {
            $field = $this->getConnectedFieldById($fieldId);
            if ($field) {
                $user = $this->getService('user');
                $socialData = (array)$user->getStorageAttribute('socialData');
                if ($field->autocomplete && isset($socialData[$field->autocomplete])) {
                    $value = $socialData[$field->autocomplete];
                }
            }
        } elseif ($this->type == 'userdata') {
            $field = $this->getConnectedFieldById($fieldId);
            if ($field) {
                $value = $field->getAutoCompleteValue();
            }
        }
        return $value;
    }

    public function getUserGroupsOptions()
    {
        $userGroups = [];
        $structureManager = $this->getService('structureManager');
        $groupsElementId = $structureManager->getElementIdByMarker('userGroups');
        $userGroupsElements = $structureManager->getElementsChildren($groupsElementId);
        if ($userGroupsElements) {
            $connectedIds = $this->getConnectedUserGroupsIds();
            foreach ($userGroupsElements as &$userGroup) {
                $item = [];
                $item['id'] = $userGroup->id;
                $item['title'] = $userGroup->getTitle();
                $item['select'] = in_array($userGroup->id, $connectedIds);
                $userGroups[] = $item;
            }
        }
        return $userGroups;
    }

    public function getFormData()
    {
        $formData = parent::getFormData();
        if ($formData['registrationFieldsIds'] === null) {
            $formData['registrationFieldsIds'] = $this->getConnectedFieldsIds();
        }
        return $formData;
    }

    public function getRegistrationFields()
    {
        $regestrationElements = [];
        $structureManager = $this->getService('structureManager');
        $connectedFields = $this->getConnectedFieldsIds();
        foreach ($structureManager->getElementsByType('registrationInput') as $regestrationElement) {
            $item = [];
            $item['id'] = $regestrationElement->id;
            $item['title'] = $regestrationElement->getTitle();
            $item['select'] = in_array($regestrationElement->id, $connectedFields);
            $regestrationElements[] = $item;
        }
        return $regestrationElements;
    }
}