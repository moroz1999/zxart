<?php

/**
 * @property string $title
 * @property int $userId
 * @property int $userGroupId
 * @property string $json
 */
class privilegesElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = ['privilege'];
    public $defaultActionName = 'show';
    public $role = 'container';
    public $usersList;
    public $userGroupsList;
    public $privileges;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['userId'] = 'text';
        $moduleStructure['userGroupId'] = 'text';
        $moduleStructure['json'] = 'jsonSerialized';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    public function getChildrenList($roles = null, $linkType = 'structure', $allowedTypes = null, $restrictLinkTypes = false)
    {
        $structureManager = $this->getService('structureManager');

        if ($childrenList = $structureManager->getElementsChildren($this->id, 'content')) {
            foreach ($childrenList as &$privilege) {
                $sortParameter[] = $privilege->structureName;
            }
            array_multisort($sortParameter, SORT_ASC, $childrenList);
        }

        return $childrenList;
    }
}