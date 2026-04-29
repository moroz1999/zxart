<?php

/**
 * Created by PhpStorm.
 * User: reneollino
 * Date: 26/09/14
 * Time: 11:15
 */
class ElementExtractionProcedure extends ExtractionProcedure
{
    public $xmlObj = null;
    protected $structureManager = null;
    private $ignoredElementTypes = null;
    private $publicLanguages = null;
    private $adminLanguages = null;
    private $multiLanguageElementsInfo = [];
    private $elementsToModify = [];

    /**
     * Method to set various arguments to the procedure (like limitation, filtering ...)
     * @param $arguments
     * @return void
     */
    public function setProcedureArguments($arguments = null)
    {
        if ($arguments) {
            foreach ($arguments as $function_name => $args) {
                if (method_exists($this, $function_name)) {
                    $this->$function_name($args);
                }
            }
        }
    }

    private function ignoredElementTypes($types)
    {
        $this->ignoredElementTypes = $types;
    }

    /**
     * Method constructs the xml
     * @return SimpleXMLElement object
     */
    public function run()
    {
        if (!$this->xmlObj) {
            $this->xmlObj = new SimpleXMLElement('<?xml version="1.0"?><procedures></procedures>');
        }

        $languagesManager = $this->getService(LanguagesManager::class);

        $this->publicLanguages = $languagesManager->getLanguagesList($this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic'));
        $this->adminLanguages = $languagesManager->getLanguagesList('adminLanguages');

        $this->structureManager = $this->getService('structureManager');

        $this->generateXml(null, $this->xmlObj, 'AddElement');
        $this->generateModifyElementsXml();

        return $this->xmlObj;
    }

    protected function generateModifyElementsXml()
    {
        foreach ($this->elementsToModify as $element) {
            $xmlChildObj = $this->xmlObj->addChild('ModifyElement');
            $xmlChildObj->addChild('targetMarker', $element->marker);
            $fieldsObj = $xmlChildObj->addChild('fields');
            $moduleData = $element->getModuleData();
            foreach ($moduleData as $languageId => $values) {
                foreach ($values as $key => $value) {
                    if ($value) {
                        $moduleDataField = $fieldsObj->addChild('field', $value);
                        $moduleDataField->addAttribute('name', $key);
                        $languageCode = null;
                        if ($languageId != 0) {
                            foreach ($this->adminLanguages as $lang) {
                                if ($lang->id == $languageId) {
                                    $languageCode = $lang->iso6393;
                                    break;
                                }
                            }
                            if (!$languageCode) {
                                foreach ($this->publicLanguages as $lang) {
                                    if ($lang->id == $languageId) {
                                        $languageCode = $lang->iso6393;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($languageCode) {
                            $moduleDataField->addAttribute('languageCode', $languageCode);
                        }
                    }
                }
            }
        }
    }

    private function generateXml($parentId, $parentXmlObj, $elementName)
    {
        $parentElement = $this->structureManager->getElementById($parentId);
        $parentElementMarker = $parentElement->marker;

        if ($elementsChildren = $this->structureManager->getElementsChildren($parentId)) {
            foreach ($elementsChildren as $element) {
                $elementStructureType = $element->structureType;
                if ($this->ignoredElementTypes && in_array($elementStructureType, $this->ignoredElementTypes)) {
                    continue;
                }
                $xmlChildObj = $parentXmlObj->addChild($elementName);
                $xmlChildObj->addChild('type', $elementStructureType);

                if ($parentElementMarker) {
                    $xmlChildObj->addChild('parentMarker', $parentElementMarker);
                } else {
                    $xmlChildObj->addChild('parentId', $parentId);
                }

                $fieldsObj = $xmlChildObj->addChild('fields');

                $structureNameField = $fieldsObj->addChild('field', $element->structureName);
                $structureNameField->addAttribute('name', 'structureName');

                if ($elementMarker = $element->marker) {
                    $structureMarkerField = $fieldsObj->addChild('field', $elementMarker);
                    $structureMarkerField->addAttribute('name', 'marker');
                }

                if ($element->marker) {
                    $this->elementsToModify[] = $element;
                }

                $moduleData = $element->getModuleData();
                if (!empty($moduleData[0])) {
                    foreach ($moduleData[0] as $key => $value) {
                        if ($value) {
                            $moduleDataField = $fieldsObj->addChild('field', $value);
                            $moduleDataField->addAttribute('name', $key);
                        }
                    }
                }
                if ($childElements = $this->structureManager->getElementsChildren($element->id)) {
                    $childrenXmlObj = $xmlChildObj->addChild('children');
                    $this->generateXml($element->id, $childrenXmlObj, 'child');
                }
            }
        }
    }
}