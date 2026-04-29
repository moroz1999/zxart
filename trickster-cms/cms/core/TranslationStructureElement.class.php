<?php

abstract class TranslationStructureElement extends structureElement
{
    protected $replaceMissingLanguageData = false;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['valueType'] = 'text';
        $moduleStructure['valueText'] = 'text';
        $moduleStructure['valueTextarea'] = 'textarea';
        $moduleStructure['valueHtml'] = 'html';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'valueText';
        $multiLanguageFields[] = 'valueTextarea';
        $multiLanguageFields[] = 'valueHtml';
    }

    public function getTranslationData()
    {
        $translationData = [];
        foreach ($this->getModuleDataObjects() as $languageId => $moduleData) {
            if ($moduleData->valueType){
                if ($chunk = $this->getDataChunk('value' . ucfirst($moduleData->valueType), $languageId)) {
                    $translationData[$this->structureName][$languageId] = $chunk->getDisplayValue();
                }
            }
        }
        return $translationData;
    }

    public function getCode()
    {
        return $this->structureName;
    }

    public function getTranslation()
    {
        switch ($this->valueType) {
            case "html":
                return $this->valueHtml;
                break;
            case "textarea":
                return $this->valueTextarea;
                break;
            default:
                return $this->valueText;
                break;
        }
    }

    public function getTitle()
    {
        return $this->structureName;
    }

    public function getSearchTitle()
    {
        $title = $this->getTitle();

        if ($parentGroup = $this->getCurrentParentElement()) {
            $title = $parentGroup->getTitle() . '.' . $title;
        }
        return $title;
    }
}