<?php

class adminTranslationsGroupElement extends TranslationsGroupStructureElement
{
    use SortedChildrenListTrait;
    protected $allowedTypes = ['adminTranslation'];

    public function getChildrenList($roles = null, $linkType = 'structure', $allowedTypes = null, $restrictLinkTypes = false)
    {
        $childrenList = parent::getChildrenList();
        if ($childrenList) {
            $sortParameter = [];
            foreach ($childrenList as &$childElement) {
                $sortParameter[] = $childElement->structureName;
            }
            array_multisort($sortParameter, SORT_ASC, $childrenList);
        }
        return $childrenList;
    }
}