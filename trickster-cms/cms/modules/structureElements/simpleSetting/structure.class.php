<?php

class simpleSettingElement extends structureElement
{
    public $dataResourceName = 'module_simplesetting';
    protected $allowedTypes = [];
    public $defaultActionName = 'showForm';
    public $role = 'content';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['value'] = 'text';
    }

    public function getSettingData()
    {
        $languagesManager = $this->getService(LanguagesManager::class);
        $settingData = [];
        $languages = $languagesManager->getLanguagesIdList();
        foreach ($languages as &$languageId) {
            $settingData[$this->structureName][$languageId] = $this->getModuleDataObject()->value;
        }
        return $settingData;
    }

    public function getTitle()
    {
        return $this->structureName;
    }

    public function deleteElementData()
    {
        parent::deleteElementData();
        $settingsManager = $this->getService(settingsManager::class);
        $settingsManager->generateSettingsFile();
    }
}