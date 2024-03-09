<?php

class loginElement extends menuDependantStructureElement
{
    public $dataResourceName = 'module_login';
    public $defaultActionName = 'show';
    public $role = 'content';
    public $hidden = false;
    protected $registrationForm;
    protected $userDataForm;
    protected $passwordReminderForm;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['remember'] = 'text';
        $moduleStructure['userName'] = 'text';
        $moduleStructure['password'] = 'password';
        $moduleStructure['description'] = 'html';
    }

    public function getRegistrationForm()
    {
        if (is_null($this->registrationForm)) {
            $structureManager = $this->getService('structureManager');
            $languagesManager = $this->getService('LanguagesManager');
            if ($elements = $structureManager->getElementsByType('registration', $languagesManager->getCurrentLanguageId())
            ) {
                foreach ($elements as $element) {
                    if ($element->type == 'registration') {
                        $this->registrationForm = $element;
                        break;
                    }
                }
            }
        }
        return $this->registrationForm;
    }

    public function getRegistrationFormUrl()
    {
        $registrationFormUrl = false;
        if ($registrationForm = $this->getRegistrationForm()) {
            $registrationFormUrl = $registrationForm->URL;
        }
        return $registrationFormUrl;
    }

    public function getUserDataForm()
    {
        if (is_null($this->userDataForm)) {
            $structureManager = $this->getService('structureManager');
            $languagesManager = $this->getService('LanguagesManager');
            if ($elements = $structureManager->getElementsByType('registration', $languagesManager->getCurrentLanguageId())
            ) {
                foreach ($elements as $element) {
                    if ($element->type == 'userdata') {
                        $this->userDataForm = $element;
                        break;
                    }
                }
            }
        }
        return $this->userDataForm;
    }

    public function getUserDataFormUrl()
    {
        $userDataFormUrl = false;
        if ($userDataForm = $this->getUserDataForm()) {
            $structureManager = $this->getService('structureManager');
            if ($userDataFormContainerElement = $structureManager->getElementsFirstParent($userDataForm->id)) {
                $userDataFormUrl = $userDataFormContainerElement->URL;
            }
        }
        return $userDataFormUrl;
    }

    public function getPasswordReminderForm()
    {
        if (is_null($this->passwordReminderForm)) {
            $structureManager = $this->getService('structureManager');
            $languagesManager = $this->getService('LanguagesManager');
            if ($elements = $structureManager->getElementsByType('passwordReminder', $languagesManager->getCurrentLanguageId())
            ) {
                foreach ($elements as $element) {
                    $this->passwordReminderForm = $element;
                    break;
                }
            }
        }
        return $this->passwordReminderForm;
    }

    public function getPasswordReminderFormUrl()
    {
        $passwordReminderFormUrl = false;
        if ($passwordReminderForm = $this->getPasswordReminderForm()) {
            $passwordReminderFormUrl = $passwordReminderForm->URL;
        }
        return $passwordReminderFormUrl;
    }

    public function getSocialPluginsOptions()
    {
        $result = [];
        $controller = controller::getInstance();
        $socialDataManager = $this->getService('SocialDataManager');
        $socialPlugins = $socialDataManager->getSocialPlugins();
        foreach ($socialPlugins as $element) {
            $iconUrl = '';
            if ($element->icon) {
                $iconUrl = $controller->baseURL . 'image/type:registrationSocialPluginIcon/id:'
                    . $element->icon . '/filename:' . $element->iconOriginalName;
            }
            $result[] = [
                'title' => $element->title,
                'code' => $element->getName(),
                'url' => $element->getLoginUrl(),
                'icon' => $iconUrl,
            ];
        }
        return $result;
    }

    public function displayForm()
    {
        $user = $this->getService('user');
        if ($user->userName == 'anonymous') {
            return true;
        }
        return false;
    }
}