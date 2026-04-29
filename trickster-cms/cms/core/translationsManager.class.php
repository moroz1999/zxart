<?php

use App\Paths\PathsManager;

class translationsManager extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    /** @var translationsManager */
    protected static $instance;
    protected $translationsList;
    protected $cachePath;
    protected $defaultSection;
    protected $logErrors = true;

    /**
     * @return translationsManager
     *
     * @deprecated
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new translationsManager();
        }
        return self::$instance;
    }

    /**
     *
     *
     * @deprecated
     */
    public function reset()
    {
        $this->translationsList = null;
    }

    public function __construct()
    {
        self::$instance = $this;
        $this->defaultSection = 'public_translations';
    }

    protected function getCachePath()
    {
        if ($this->cachePath === null) {
            $this->cachePath = $this->getService(PathsManager::class)->getPath('translationsCache');
        }
        return $this->cachePath;
    }

    public function getTranslationsList($sectionName = 'public_translations', $languageId = null)
    {
        static $rootMarkerPublic;

        if (is_null($languageId)) {
            if ($sectionName === 'adminTranslations') {
                $marker = 'adminLanguages';
            } else {
                if (!isset($rootMarkerPublic)) {
                    $rootMarkerPublic = $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
                }
                $marker = $rootMarkerPublic;
            }
            $languagesManager = $this->getService(LanguagesManager::class);
            $languageId = $languagesManager->getCurrentLanguageId($marker);
        }
        if (!isset($this->translationsList[$sectionName]) || !isset($this->translationsList[$sectionName][$languageId]) || is_null(
                $this->translationsList[$sectionName][$languageId]
            )
        ) {
            if (!$this->loadTranslationsList($sectionName, $languageId)) {
                $this->generateTranslationsFile($sectionName);
                $this->loadTranslationsList($sectionName, $languageId);
            }
        }

        return $this->translationsList[$sectionName][$languageId];
    }

    public function getGroupTranslationsList($groupName, $sectionName = 'public_translations', $languageId = null)
    {
        $result = [];
        if ($allTranslations = $this->getTranslationsList($sectionName, $languageId)) {
            $search = $groupName . '.';
            foreach ($allTranslations as $key => $value) {
                if (stripos($key, $search) === 0) {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    protected function loadTranslationsList($sectionName, $languageId)
    {
        $translationsList = false;
        $filePath = $this->getCachePath() . $sectionName . '_' . $languageId . '.php';
        if (file_exists($filePath)) {
            include $filePath;
        }
        if (is_array($translationsList)) {
            $this->translationsList[$sectionName][$languageId] = $translationsList;
            return true;
        }
        return false;
    }

    public function generateTranslationsFile($sectionName)
    {
        $allData = [];
        $languagesManager = $this->getService(LanguagesManager::class);
        foreach ($languagesManager->getLanguagesIdList() as $languageId) {
            // we need to ensure that all languages are used.
            // otherwise no cache file would be created for empty language and this method is called many hundreds of times.
            $allData[$languageId] = [];
        }

        $structureManager = $this->getService('adminStructureManager');
        $structureManager->setPrivilegeChecking(false);
        if ($sectionElement = $structureManager->getElementByMarker($sectionName)) {
            if ($translationsGroups = $structureManager->getElementsChildren($sectionElement->id)) {
                foreach ($translationsGroups as $translationGroup) {
                    if ($translations = $structureManager->getElementsChildren($translationGroup->id)) {
                        foreach ($translations as $translation) {
                            $translationData = $translation->getTranslationData();
                            foreach ($translationData as $translationName => $languageData) {
                                foreach ($languageData as $languageId => $value) {
                                    $allData[$languageId][$translationGroup->title . '.' . $translationName] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }
        $structureManager->setPrivilegeChecking(true);
        $this->getService(PathsManager::class)->ensureDirectory($this->getCachePath());
        foreach ($allData as $languageId => $languageData) {
            $filePath = $this->getCachePath() . $sectionName . '_' . $languageId . '.php';
            $text = $this->generateTranslationsText($languageData);
            file_put_contents($filePath, $text);
        }
    }

    protected function generateTranslationsText($languageData)
    {
        $text = '<?php

use App\Paths\PathsManager; $translationsList =  ';
        $text .= var_export($languageData, true);
        $text .= '?>';

        return $text;
    }

    public function getTranslationByName($name, $section = null, $required = true, $loggable = true, $languageId = null)
    {
        if (is_null($section)) {
            $section = $this->defaultSection;
        }
        // for empty language empty array is returned, so can't check for false or null
        if (is_array($translationsList = $this->getTranslationsList($section, $languageId))) {
            $name = strtolower($name);
            if (!empty($translationsList[$name])) {
                return $translationsList[$name];
            }

            if ($required) {
                if ($loggable && $this->logErrors) {
                    $this->logError('Missing translation ' . $name, E_NOTICE, false);
                }
                return '#' . $name . '#';
            }

            return "";
        }
        return '{translations error}';
    }

    public function setDefaultSection($sectionName)
    {
        $this->defaultSection = $sectionName;
    }

    public function enableLogging($value)
    {
        $this->logErrors = $value;
    }
}
