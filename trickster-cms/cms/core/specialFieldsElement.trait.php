<?php

trait specialFieldsElementTrait
{
    public function getDataChunk($propertyName, $languageId = null)
    {
        $dataChunk = parent::getDataChunk($propertyName, $languageId);
        if ($dataChunk && is_null($dataChunk->getFormValue())) {
            $specialFields = $this->getSpecialFields();
            if (isset($specialFields[$propertyName])) {
                $specialData = $this->getSpecialData();
                if (is_null($languageId)) {
                    $languageId = $this->getCurrentLanguage();
                }
                if (isset($specialData[$languageId][$propertyName])) {
                    if ($dataChunk instanceof ElementStorageValueHolderInterface) {
                        $dataChunk->setElementStorageValue($specialData[$languageId][$propertyName]);
                    }
                }
            }
        }
        return $dataChunk;
    }

    public function setValue($propertyName, $value, $languageId = 0)
    {
        $specialFields = $this->getSpecialFields();
        if (isset($specialFields[$propertyName])) {
            $specialData = $this->getSpecialData();
            if (isset($specialData[$languageId])) {
                $languageSpecialData = $specialData[$languageId];
                if (!$value) {
                    unset($languageSpecialData[$propertyName]);
                } else {
                    $languageSpecialData[$propertyName] = $value;
                }
                $this->specialData[$languageId] = $languageSpecialData;
                $dataObjects = $this->getDataObjectsForProperty('data');
                foreach ($dataObjects as $languageId => &$dataObject) {
                    $dataObject->data = json_encode($this->specialData[$languageId]);
                }
                if ($dataChunk = $this->getDataChunk('data', $languageId)) {
                    $jsonData = json_encode($languageSpecialData);
                    $dataChunk->setExternalValue($jsonData);
                }
            }
        }
        parent::setValue($propertyName, $value, $languageId);
    }

    public function importExternalData(
        $externalData,
        $expectedFields = [],
        $validators = [],
        $filteredLanguageId = false
    ) {
        $validated = parent::importExternalData($externalData, $expectedFields, $validators, $filteredLanguageId);

        // update specialdata
        $specialFields = $this->getSpecialFields();
        $specialData = $this->getSpecialData();
        foreach ($this->getModuleDataObjects() as $dataObject) {
            foreach ($specialFields as $fieldName => &$specialField) {
                $specialFieldData = $dataObject->$fieldName;
                if (!is_null($specialFieldData)) {
                    $specialData[$dataObject->languageId][$fieldName] = $specialFieldData;
                }
            }
        }
        // save "data" field contents for each language
        foreach ($specialData as $languageId => &$languageSpecialData) {
            if ($languageSpecialData) {
                // clean entries
                foreach ($languageSpecialData as $fieldName => $specialDataItem) {
                    if (!isset($specialFields[$fieldName])) {
                        unset($languageSpecialData[$fieldName]);
                    }
                }
            }
            // save
            if ($moduleDataObject = $this->getModuleDataObject($languageId)) {
                $moduleDataObject->data = json_encode($languageSpecialData);
            }
        }
        return $validated;
    }

    public function getSpecialData($targetLanguageId = null)
    {
        if (is_null($this->specialData)) {
            $this->specialData = [];
            if ($languages = $this->getLanguagesList()) {
                foreach ($languages as $languageId) {
                    if ($chunk = $this->getDataChunk('data', $languageId)) {
                        $this->specialData[$languageId] = json_decode($chunk->getStorageValue(), true);
                    }
                }
            }
        }
        if ($targetLanguageId && isset($this->specialData[$targetLanguageId])) {
            return $this->specialData[$targetLanguageId];
        } else {
            return $this->specialData;
        }
    }

    public function getSpecialFields()
    {
        return [];
    }

    public function getSpecialDataByKey($key, $languageId = '')
    {
        $languageData = [];
        $specialData = $this->getSpecialData();
        if ($specialData) {
            if ($languageId) {
                $languageData = isset($specialData[$languageId]) ? $specialData[$languageId] : [];
            } else {
                $languageData = $specialData ? reset($specialData) : [];
            }
        }
        return isset($languageData[$key]) ? $languageData[$key] : '';
    }
}