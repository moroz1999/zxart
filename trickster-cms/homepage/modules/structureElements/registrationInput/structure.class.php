<?php

use App\Paths\PathsManager;
use App\Users\CurrentUserService;

/**
 * @property string $title
 * @property string $fieldName
 * @property string $fieldType
 * @property string $dataChunk
 * @property bool $required
 * @property string $validator
 * @property string $autocomplete
 */
class registrationInputElement extends formFieldStructureElement
{
    use AutocompleteOptionsTrait;
    public $dataResourceName = 'module_form_field';
    public $defaultActionName = 'show';
    public $role = 'content';
    const FIELD_LINK_TYPE = 'registrationField';
    public $value = '';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['fieldName'] = 'text';
        $moduleStructure['fieldType'] = 'text';
        $moduleStructure['dataChunk'] = 'text';
        $moduleStructure['required'] = 'checkbox';
        $moduleStructure['validator'] = 'text';
        $moduleStructure['autocomplete'] = 'text';
        $moduleStructure['registrationForms'] = 'numbersArray';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    public function getInputRoles()
    {
        return [
            'userName',
            'password',
            'passwordRepeat',
            'email',
        ];
    }

    public function validate()
    {
        $required = $this->required || $this->autocomplete == 'userName' || $this->autocomplete == 'email';
        $valid = !$required || $this->value !== '';
        if ($valid && $this->autocomplete == 'email') {
            $pathsManager = $this->getService(PathsManager::class);
            $fileDirectory = $pathsManager->getRelativePath('validators');
            if ($fileName = $pathsManager->getIncludeFilePath($fileDirectory . 'email.class.php')) {
                include_once($fileName);
                $mailValidator = new emailValidator();
                $valid = $mailValidator->execute($this->value);
            }
        }
        return $valid;
    }

    public function getInputType()
    {
        $type = 'text';
        if ($this->autocomplete == 'password' || $this->autocomplete == 'passwordRepeat') {
            $type = 'password';
        }
        return $type;
    }

    public function getAutoCompleteValue()
    {
        $value = '';

        $autocomplete = $this->autocomplete;
        if ($autocomplete) {
            $currentUserService = $this->getService(CurrentUserService::class);
            $user = $currentUserService->getCurrentUser();
            switch ($autocomplete) {
                case 'userName':
                case 'email':
                    $value = $user->$autocomplete;
            }
        }
        return $value;
    }

    public function getUserDataForms()
    {
        $registrationElements = [];
        $structureManager = $this->getService('structureManager');
        $connectedFormsIds = $this->getConnectedFormsIds();
        foreach ($structureManager->getElementsByType('registration') as $registrationElement) {
            $item = [];
            $item['id'] = $registrationElement->id;
            $item['title'] = $registrationElement->getTitle();
            $item['select'] = in_array($registrationElement->id, $connectedFormsIds);
            $registrationElements[] = $item;
        }
        return $registrationElements;
    }

    public function getFormData()
    {
        $formData = parent::getFormData();
        if ($formData['registrationForms'] === null) {
            $formData['registrationForms'] = $this->getConnectedFormsIds();
        }
        return $formData;
    }

    public function getConnectedFormsIds()
    {
        $linksManager = $this->getService(linksManager::class);
        return $linksManager->getConnectedIdList($this->id, self::FIELD_LINK_TYPE, 'child');
    }
}



