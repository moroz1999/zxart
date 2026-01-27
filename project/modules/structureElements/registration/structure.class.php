<?php

class registrationElement extends menuDependantStructureElement
{
    const string FIELD_LINK_TYPE = 'registrationField';
    const string USER_GROUP_LINK_TYPE = 'registrationUserGroup';
    public $dataResourceName = 'module_registration';
    public $defaultActionName = 'show';
    public $role = 'content';
    public $socialConnectionSuccess = false;
    protected $connectedFields;
    protected $connectedFieldsIndex;
    protected $dynamicFieldsErrors = [];
    public $resultMessage;
    public $errorMessage;
    /**
     * @return void
     */
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

    /**
     * @return (bool|mixed|string)[][]
     *
     * @psalm-return list{0?: array{title: mixed, code: mixed, url: mixed, connected: bool, icon: string},...}
     */
    public function getSocialPluginsOptions(): array
    {
        $result = [];
        $existingConnections = [];
        $user = $this->getService(user::class);
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
            $result = $structureManager->getElementsChildren($fieldsElement->getId());
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
        return $this->getService('linksManager')->getConnectedIdList($this->getId(), self::FIELD_LINK_TYPE, 'parent');
    }

    public function getConnectedUserGroupsIds()
    {
        return $this->getService('linksManager')->getConnectedIdList($this->getId(), self::USER_GROUP_LINK_TYPE, 'parent');
    }

    public function setDynamicFieldError($fieldId): void
    {
        $this->dynamicFieldsErrors[$fieldId] = true;
    }

    public function getDynamicFieldError($fieldId): bool
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
                $user = $this->getService(user::class);
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

    /**
     * @return (bool|mixed)[][]
     *
     * @psalm-return list{0?: array{id: mixed, title: mixed, select: bool},...}
     */
    public function getUserGroupsOptions(): array
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

    /**
     * @return (bool|mixed)[][]
     *
     * @psalm-return list{0?: array{id: mixed, title: mixed, select: bool},...}
     */
    public function getRegistrationFields(): array
    {
        $regestrationElements = [];
        $structureManager = $this->getService('structureManager');
        $connectedFields = $this->getConnectedFieldsIds();
        foreach ($structureManager->getElementsByType('registrationInput') as $regestrationElement) {
            $item = [];
            $item['id'] = $regestrationElement->getId();
            $item['title'] = $regestrationElement->getTitle();
            $item['select'] = in_array($regestrationElement->getId(), $connectedFields);
            $regestrationElements[] = $item;
        }
        return $regestrationElements;
    }
}