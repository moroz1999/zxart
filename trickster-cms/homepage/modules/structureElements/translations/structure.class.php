<?php

class translationsElement extends TranslationsStructureElement
{
    use SortedChildrenListTrait;
    use AutoMarkerTrait;
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = ['translationsGroup'];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    protected function initialize()
    {
        $this->translationsLanguagesGroup = 'public_root';
    }

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['xmlFile'] = 'file';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    protected function getThemeCodes()
    {
        $configManager = $this->getService(ConfigManager::class);
        $publicThemeName = $configManager->get('main.publicTheme');
        return ['projectEmail', 'projectPdf', 'projectRss', $publicThemeName];
    }
}

