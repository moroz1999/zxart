<?php

class tagElement extends structureElement implements JsonDataProvider
{
    use JsonDataProviderElement;

    public $dataResourceName = 'module_tag';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $translations;
    protected $isUntranslated;
    protected $pictures;
    protected $tunes;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['amount'] = 'text';
        $moduleStructure['userId'] = 'text';
        $moduleStructure['synonym'] = 'text';
        $moduleStructure['description'] = 'text';
        $moduleStructure['joinTag'] = 'text';
        $moduleStructure['verified'] = 'checkbox';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
        $multiLanguageFields[] = 'synonym';
        $multiLanguageFields[] = 'description';
    }

    public function updateAmount()
    {
        $linksManager = $this->getService('linksManager');
        $this->amount = count($linksManager->getConnectedIdList($this->id, 'tagLink', 'parent'));
        $this->persistElementData();
    }

    public function getFontSize($maxAmount = 10)
    {
        $min = 1;
        $max = 4;

        if ($this->amount > $this->getService('ConfigManager')->get('zx.maxTagsAmount')) {
            return $min + ($max - $min) * (99) / $maxAmount;
        } else {
            return $min + ($max - $min) * ($this->amount - 1) / $maxAmount;
        }
    }

    public function updateTagsListLinks()
    {
        // connect all tagslists configured to show all tags
        $structureManager = $this->getService('structureManager');
        if ($tagsLists = $structureManager->getElementsByType('tagsList')) {
            $linksManager = $this->getService('linksManager');
            foreach ($tagsLists as $tagsList) {
                $linksManager->linkElements($tagsList->id, $this->id, 'tagsList');
            }
        }
    }

    public function persistElementData()
    {
        parent::persistElementData();
        $this->updateTagsListLinks();
    }

    public function getTranslations()
    {
        if ($this->translations === null) {
            $this->translations = [];
            foreach ($this->getLanguagesList() as $languageId) {
                $this->translations[$languageId] = $this->getValue('title', $languageId);
            }
        }
        return $this->translations;
    }

    public function detectUntranslated()
    {
        if ($this->isUntranslated === null) {
            $this->isUntranslated = false;
            $index = [];
            foreach ($this->getTranslations() as $languageId => &$translation) {
                if (isset($index[$translation])) {
                    $this->isUntranslated = true;
                    break;
                }
                $index[$translation] = true;
            }
        }
        return $this->isUntranslated;
    }

    public function getItems()
    {
        $sectionsLogics = $this->getService('SectionLogics');;
        if (($type = $sectionsLogics->getArtItemsType()) == 'graphics') {
            $this->pictures = $this->loadElementsByType('zxPicture');
            return $this->pictures;
        } elseif ($type == 'music') {
            $this->tunes = $this->loadElementsByType('zxMusic');
            return $this->tunes;
        }
        return false;
    }

    protected function loadElementsByType($type)
    {
        $elements = [];
        $linksManager = $this->getService('linksManager');
        $structureManager = $this->getService('structureManager');
        if ($idList = $linksManager->getConnectedIdList($this->id, 'tagLink', 'parent')) {
            foreach ($idList as $id) {
                if (($connectedElement = $structureManager->getElementById(
                        $id
                    )) && $connectedElement->structureType == $type) {
                    $elements[] = $connectedElement;
                }
            }
        }

        $sort = [];
        foreach ($elements as $element) {
            $sort[] = strtolower($element->title);
        }
        array_multisort($sort, SORT_ASC, $elements);

        return $elements;
    }

}