<?php

/**
 * Created by PhpStorm.
 * User: reneollino
 * Date: 26/09/14
 * Time: 11:15
 */
class TranslationExtractionProcedure extends ExtractionProcedure
{
    public $xmlObj = null;
    private $translationMarkers = null;

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

    private function setTranslationMarker($marker)
    {
        $this->translationMarkers = $marker;
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

        foreach ($this->translationMarkers as $translationMarker) {
            $this->convertTranslationMarkerToXml($translationMarker);
        }

        return $this->xmlObj;
    }

    private function convertTranslationMarkerToXml($marker)
    {
        $structureManager = $this->getService('structureManager');

        if ($translationsElement = $structureManager->getElementByMarker($marker)) {
            if ($marker == 'adminTranslations') {
                $languageMarker = 'adminLanguages';
                $translationType = 'adminTranslation';
            } else {
                $languageMarker = $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
                $translationType = 'translation';
            }
            $languagesList = $this->getService(LanguagesManager::class)->getLanguagesList($languageMarker);

            if ($translationGroups = $structureManager->getElementsChildren($translationsElement->id)) {
                foreach ($translationGroups as $translationGroup) {
                    $translationElements = $structureManager->getElementsChildren($translationGroup->id);

                    foreach ($translationElements as $translationElement) {
                        $code = $translationElement->getCode();
                        $valueType = $translationElement->valueType;
                        $values = $translationElement->getTranslationData();

                        $xmlChildObj = $this->xmlObj->addChild('AddTranslation');
                        $xmlChildObj->addChild('type', $translationType);
                        $xmlChildObj->addChild('code', $translationGroup->title . '.' . $code);
                        $xmlChildObj->addChild('valueType', $valueType);
                        $valuesXmlObj = $xmlChildObj->addChild('values');

                        foreach ($values[$code] as $langId => $translationString) {
                            $translationStringXmlObj = $valuesXmlObj->addChild('value', $translationString);
                            $languageCode = $langId;
                            foreach ($languagesList as $lang) {
                                if ($lang->id == $langId) {
                                    $languageCode = $lang->iso6393;
                                    break;
                                }
                            }
                            $translationStringXmlObj->addAttribute('languageCode', $languageCode);
                        }
                    }
                }
            }
        }
    }
}