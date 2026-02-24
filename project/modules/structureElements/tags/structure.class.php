<?php

class tagsElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $allowedTypes = ['tag'];
    public $defaultActionName = 'show';
    public $role = 'container';

    protected $tagsList;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
        $multiLanguageFields[] = 'title';
    }

    public function getTagsList()
    {
        if (is_null($this->tagsList)) {
            $this->tagsList = [];
            $structureManager = $this->getService('structureManager');
            if ($tagsList = $structureManager->getElementsChildren($this->getId())) {
                $sortParameter = [];
                foreach ($tagsList as $child) {
                    $sortParameter[] = mb_strtolower($child->title);
                }
                array_multisort($sortParameter, SORT_ASC, $tagsList);

                $controller = controller::getInstance();
                if ($controller->getParameter('filter') == 'rare') {
                    foreach ($tagsList as $element) {
                        if ($element->amount <= 1) {
                            $this->tagsList[] = $element;
                        }
                    }
                } elseif ($controller->getParameter('filter') == 'nonverified') {
                    foreach ($tagsList as $element) {
                        if (!$element->verified) {
                            $this->tagsList[] = $element;
                        }
                    }
                } elseif ($controller->getParameter('filter') == 'duplicates') {
                    foreach ($tagsList as $element) {
                        if ($id = $this->searchDuplicates($element)) {
                            if ($duplicate = $structureManager->getElementById($id)) {
                                $element->duplicateTag = $duplicate;
                                $this->tagsList[] = $element;
                            }
                        }
                    }
                } elseif ($controller->getParameter('filter') == 'untranslated') {
                    foreach ($tagsList as $element) {
                        if ($element->detectUntranslated()) {
                            $this->tagsList[] = $element;
                        }
                    }
                } else {
                    $this->tagsList = $tagsList;
                }
            }
        }

        return $this->tagsList;
    }

    public function searchDuplicates($tagElement)
    {
        $db = $this->getService('db');
        $tagId = $tagElement->getId();
        foreach ($tagElement->getTranslations() as $languageId => &$translation) {
            if ($id = $db->table('module_tag')->select('id')
                ->where('id', '!=', $tagId)
                ->where(
                    function ($query) use ($tagId, $translation) {
                        $query->where('title', '=', $translation)->orWhereRaw(
                            'match (synonym) against (? in boolean mode)',
                            [$translation]
                        );
                    }
                )
                ->limit(1)
                ->first()
            ) {
                return $id['id'];
            }
        }

        return false;
    }

    /**
     * @psalm-return int<0, max>
     */
    public function getTagsListCount(): int
    {
        return count($this->getTagsList());
    }

    public function getPublicLanguages()
    {
        return $this->getService(LanguagesManager::class)->getLanguagesList('public_root');
    }
}


