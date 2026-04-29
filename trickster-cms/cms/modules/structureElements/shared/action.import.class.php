<?php

class importShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $contents = $structureElement->getDataChunk("xmlFile")->getUploadedContents();
            if ($xmlData = simplexml_load_string($contents)) {
                foreach ($xmlData->children() as $elementXML) {
                    $this->importElement($elementXML, $structureElement->id);
                }
            }
        }
        $structureElement->executeAction('show');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['xmlFile'];
    }

    public function setValidators(&$validators)
    {
    }

    protected function importElement($elementXML, $parentId)
    {
        $structureData = [];
        foreach ($elementXML->structureData->children() as $value) {
            $fieldName = (string)$value->attributes()->name;
            $structureData[$fieldName] = (string)$value;
        }
        $moduleData = [];
        foreach ($elementXML->moduleData->children() as $languageXML) {
            $languageId = (string)$languageXML->attributes()->id;
            foreach ($languageXML->children() as $value) {
                $fieldName = (string)$value->attributes()->name;
                $moduleData[$languageId][$fieldName] = (string)$value;
            }
        }
        if ($newElement = $this->getElement($structureData['structureType'], $structureData['structureName'], $parentId, $moduleData)
        ) {
            unset($structureData['dateCreated']);
            unset($structureData['dateModified']);
            if ($changed = $newElement->importExportedData($structureData, $moduleData)) {
                $newElement->prepareActualData();
                $newElement->persistElementData();
            }
            foreach ($elementXML->childrenData->children() as $childXML) {
                $this->importElement($childXML, $newElement->id);
            }
        }
    }

    protected function getElement($structureType, $structureName, $parentId, $moduleData)
    {
        $newElement = false;
        $structureManager = $this->getService('structureManager');
        $childrenList = $structureManager->getElementsChildren($parentId);

        $title = false;
        if ($structureType == 'translationsGroup' || $structureType == 'adminTranslationsGroup') {
            if ($langData = reset($moduleData)) {
                if (isset($langData['title'])) {
                    $title = $langData['title'];
                }
            }
        }

        foreach ($childrenList as &$childElement) {
            if (($title && $childElement->title == $title) || $childElement->structureName == $structureName) {
                if (method_exists($childElement, 'getTranslation') && $childElement->getTranslation() != "") {
                    return false;
                }
                $newElement = $childElement;
                break;
            }
        }
        if (!$newElement) {
            $newElement = $structureManager->createElement($structureType, 'showForm', $parentId);
        }
        return $newElement;
    }
}