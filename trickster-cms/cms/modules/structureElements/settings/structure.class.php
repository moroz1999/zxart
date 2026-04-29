<?php

class settingsElement extends structureElement
{
    use AutoMarkerTrait;
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = ['simpleSetting'];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    public function getChildrenList($roles = null, $linkType = 'structure', $allowedTypes = null, $restrictLinkTypes = false)
    {
        $structureManager = $this->getService('structureManager');

        $childrenList = $structureManager->getElementsChildren($this->id, 'content');
        $sortParameter = [];
        foreach ($childrenList as &$privilege) {
            $sortParameter[] = $privilege->structureName;
        }
        array_multisort($sortParameter, SORT_ASC, $childrenList);

        return $childrenList;
    }
}
