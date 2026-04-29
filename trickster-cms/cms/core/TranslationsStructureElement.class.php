<?php

use App\Paths\PathsManager;

abstract class TranslationsStructureElement extends structureElement
{
    protected $translationsLanguagesGroup;
    protected $incompleteTranslations;

    protected function makeComparer()
    {
        $designThemesManager = $this->getService(DesignThemesManager::class);
        $translationsCodesComparer = new TranslationsCodesComparer();
        foreach ($this->getThemeCodes() as $themeCode) {
            $theme = $designThemesManager->getTheme($themeCode);

            $templatePaths = $theme->getTemplateResources();
            foreach ($templatePaths as &$path) {
                $translationsCodesComparer->addFilePath($path, "smarty");
            }
            $javascriptPaths = $theme->getJavascriptPaths();
            foreach ($javascriptPaths as &$path) {
                $translationsCodesComparer->addFilePath($path, "javascript");
            }
        }
        $pathsManager = $this->getService(PathsManager::class);
        $corePath = $pathsManager->getRelativePath('core');
        $modulesPath = $pathsManager->getRelativePath('modules');
        foreach ($pathsManager->getIncludePaths() as $path) {
            $translationsCodesComparer->addFilePath($path . $corePath, "php");
            $translationsCodesComparer->addFilePath($path . $modulesPath, "php");
        }
        return $translationsCodesComparer;
    }

    public function getRedundantTranslations()
    {
        $result = [];

        $translationsCodesComparer = $this->makeComparer();

        if ($translationsIndex = $this->getTranslationsElementsIndex()) {
            $translationsCodesComparer->setTranslationsIndex($translationsIndex);
            $unusedList = $translationsCodesComparer->getUnusedTranslationCodes();
            foreach ($translationsIndex as $code => $element) {
                if (in_array($code, $unusedList)) {
                    $result[] = $element;
                }
            }
        }
        return $result;
    }

    public function searchMissingTranslations()
    {
        $result = [];
        $translationsCodesComparer = $this->makeComparer();

        if ($translationsIndex = $this->getTranslationsElementsIndex()) {
            $translationsCodesComparer->setTranslationsIndex($translationsIndex);
            $result = $translationsCodesComparer->getEmptyTranslationsCodes();
        }
        return $result;
    }

    abstract protected function getThemeCodes();

    public function getTranslationsElementsIndex()
    {
        $translationsIndex = [];
        if ($groups = $this->getChildrenList()) {
            foreach ($groups as &$groupElement) {
                if ($translationsList = $groupElement->getTranslations()) {
                    foreach ($translationsList as &$translationElement) {
                        $translationElement->group = $groupElement;
                        $code = $groupElement->title . '.' . $translationElement->getCode();
                        $translationsIndex[$code] = $translationElement;
                    }
                }
            }
        }
        return $translationsIndex;
    }

    public function getIncompleteTranslations()
    {
        // TODO perhaps give translations methods to check incompleteness,
        // give groups methods to find and store  missing translations.
        if (is_null($this->incompleteTranslations)) {
            $this->incompleteTranslations = [];
            $translationsGroups = $this->getChildrenList();
            $languagesManager = $this->getService(LanguagesManager::class);

            $languages = $languagesManager->getLanguagesList($this->translationsLanguagesGroup);
            $languagesIndex = [];
            foreach ($languages as &$language) {
                $languagesIndex[$language->id] = $language;
            }

            foreach ($translationsGroups as &$translationsGroup) {
                $translations = $translationsGroup->getChildrenList();
                foreach ($translations as &$translation) {
                    $translation->group = $translationsGroup;
                    $moduleDataObjects = $translation->getModuleDataObjects();
                    foreach ($moduleDataObjects as $languageId => &$moduleData) {
                        if (!$moduleData->valueText && !$moduleData->valueTextarea && !$moduleData->valueHtml) {
                            if (is_null($translation->missingLanguages)) {
                                $this->incompleteTranslations[] = $translation;
                                $translation->missingLanguages = [];
                            }
                            $translation->missingLanguages[] = $languagesIndex[$languageId]->iso6393;
                        }
                    }
                }
            }
        }
        return $this->incompleteTranslations;
    }

    /**
     * @param $code
     * @param bool $createMissing
     * @return translationsGroupElement
     */
    public function getGroupByCode($code, $createMissing = false)
    {
        $code = strtolower($code);
        $resultElement = false;
        $translationsGroups = $this->getChildrenList();
        foreach ($translationsGroups as &$translationsGroup) {
            if (strtolower($translationsGroup->title) == $code) {
                $resultElement = $translationsGroup;
                break;
            }
        }
        if (!$resultElement) {
            $structureManager = $this->getService('structureManager');
            if ($types = $this->getAllowedTypes()) {
                $resultElement = $structureManager->createElement(reset($types), 'showForm', $this->id);
                $resultElement->prepareActualData();
                $resultElement->title = $code;
                $resultElement->persistElementData();
            }
        }
        return $resultElement;
    }

    public function createTranslation($translationCode, $groupElementId, $data)
    {
        $translationCode = strtolower($translationCode);
        $structureManager = $this->getService('structureManager');
        if ($groupElement = $structureManager->getElementById($groupElementId)) {
            $types = $groupElement->getAllowedTypes();
            $resultElement = $structureManager->createElement(reset($types), 'showForm', $groupElement->id);
            $resultElement->prepareActualData();
            $resultElement->structureName = $translationCode;
            $resultElement->persistElementData();
        }
    }
}
