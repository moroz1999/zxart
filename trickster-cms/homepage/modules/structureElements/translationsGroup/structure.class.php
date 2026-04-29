<?php

class translationsGroupElement extends TranslationsGroupStructureElement
{
    protected $allowedTypes = ['translation'];

    public function getChildrenList($roles = null, $linkType = 'structure', $allowedTypes = null, $restrictLinkTypes = false)
    {
        $structureManager = $this->getService('structureManager');

        $childrenList = $structureManager->getElementsChildren($this->id, 'content');
        $sortParameter = [];
        foreach ($childrenList as $element) {
            $sortParameter[] = $element->structureName;
        }
        array_multisort($sortParameter, SORT_ASC, $childrenList);

        return $childrenList;
    }
}

