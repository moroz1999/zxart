<?php

class LanguagesManager extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    protected $sessionStorageEnabled = false;
    protected $currentLanguageInfo;
    protected $languagesList = [];
    protected $languagesIdList = [];
    protected $map = [];
    protected $ciMap = [];
    protected $shortToLongCodes = [
        'et' => 'est',
        'ru' => 'rus',
        'en' => 'eng',
        'lv' => 'lat',
        'lt' => 'lit',
        'fi' => 'fin',
        'be' => 'bel',
    ];
    /**
     * @var ServerSessionManager
     */
    protected $serverSessionManager;

    /**
     * @param ServerSessionManager $serverSessionManager
     */
    public function setServerSessionManager($serverSessionManager)
    {
        $this->serverSessionManager = $serverSessionManager;
    }

    public function reset()
    {
        $this->currentLanguageInfo = null;
        $this->languagesList = [];
        $this->languagesIdList = [];
        $this->map = [];
        $this->ciMap = [];
    }

    public function getCurrentLanguageCode($groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic');
        if (!isset($this->currentLanguageInfo[$groupName])) {
            $this->detectCurrentLanguageCode($groupName);
        }
        return $this->currentLanguageInfo[$groupName]->iso6393;
    }

    public function getDefaultLanguageCode($groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic');
        if ($defaultLanguage = $this->getDefaultLanguage($groupName)) {
            return $defaultLanguage->iso6393;
        }
        return false;
    }

    public function getCurrentLanguage($groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
        foreach ($this->getLanguagesList($groupName) as $language) {
            if ($language->id == $this->getCurrentLanguageId($groupName)) {
                return $language;
            }
        }
        return false;
    }

    public function getLanguageByCode($code, $groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
        foreach ($this->getLanguagesList($groupName) as $language) {
            if ($language->iso6393 == $code) {
                return $language;
            }
        }
        return false;
    }

    public function getCurrentLanguageId($groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic');
        if (!isset($this->currentLanguageInfo[$groupName])) {
            $this->detectCurrentLanguageCode($groupName);
        }
        if (isset($this->currentLanguageInfo[$groupName]) && is_object($this->currentLanguageInfo[$groupName])) {
            return $this->currentLanguageInfo[$groupName]->id;
        }
        return false;
    }

    public function getLanguageId($code, $groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic');
        if ($language = $this->getLanguageByCode($code, $groupName)) {
            return $language->id;
        }
        return false;
    }

    public function getLanguagesList($groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic');
        if (!isset($this->languagesList[$groupName])) {
            $collection = persistableCollection::getInstance('module_language');
            $this->languagesList[$groupName] = $collection->load(['group' => $groupName]);
            if ($this->languagesList[$groupName]) {
                $this->sortLanguages($this->languagesList[$groupName]);
            }
        }

        return $this->languagesList[$groupName];
    }

    public function getLanguagesIdList($groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic');
        if (!isset($this->languagesIdList[$groupName])) {
            $languagesIdList = [];
            $info = $this->getLanguagesList($groupName);
            foreach ($info as &$language) {
                $languagesIdList[] = $language->id;
            }
            $this->languagesIdList[$groupName] = $languagesIdList;
        }

        return $this->languagesIdList[$groupName];
    }

    /**
     * returns default language: either it's set in config or the first one available
     * @param $groupName
     * @return object
     */
    public function getDefaultLanguage($groupName)
    {
        $result = null;
        if ($languageCode = $this->getCodeFromConfig()) {
            $map = $this->getLanguagesCiMap($groupName);
            $result = isset($map[$languageCode]) ? $map[$languageCode] : null;
        }
        if (!$result) {
            $result = $this->getFirstAvailableLanguage($groupName);
        }
        return $result;
    }

    protected function detectCurrentLanguageCode($groupName = null)
    {
        $publicGroup = $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
        $groupName = $groupName ?: $publicGroup;

        if ($groupName === $publicGroup) {
            $languageCode = $this->getCodeFromURI();
            if ($this->checkLanguageCode($languageCode, $groupName)) {
                goto finish;
            }
        }
        $languageCode = $this->getCodeFromSession($groupName);
        if ($this->checkLanguageCode($languageCode, $groupName)) {
            goto finish;
        }
        $languageCode = $this->getCodeFromCookies($groupName);
        if ($groupName == 'adminLanguages') {
            $checkHidden = true;
        } else {
            $checkHidden = false;
        }
        if ($this->checkLanguageCode($languageCode, $groupName, $checkHidden)) {
            goto finish;
        }
        $languageCode = $this->getCodeFromConfig();
        if ($this->checkLanguageCode($languageCode, $groupName, $checkHidden)) {
            goto finish;
        }
        $headerLanguages = $this->parseLanguagesFromAcceptHeader();
        foreach ($headerLanguages as $languageCode) {
            if (strlen($languageCode) === 2) {
                if (!isset($this->shortToLongCodes[$languageCode])) {
                    continue;
                }
                $languageCode = $this->shortToLongCodes[$languageCode];
            }
            if ($this->checkLanguageCode($languageCode, $groupName, false)) {
                goto finish;
            }
        }
        $languageCode = $this->getFirstAvailableCode($groupName);
        finish:
        if ($languageCode) {
            $this->setCurrentLanguageCode($languageCode, $groupName);
        }
    }

    /**
     * Parses Accept-Language header, returns list of langs ordered by importance descending
     * Header example: "en-US,en;q=0.8,et;q=0.6,ru;q=0.4"
     * @link https://tools.ietf.org/html/rfc7231#section-5.3.5
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
     * @return string[]
     */
    protected function parseLanguagesFromAcceptHeader()
    {
        $header = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
            ? (string)$_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        $header = str_replace(' ', '', $header);
        if ($header === '' || $header === '*') {
            return [];
        }
        $parts = explode(',', strtolower($header));
        $languages = [];
        $qualities = [];
        foreach ($parts as $part) {
            $lang = trim($part);
            $q = 1;
            $i = strpos($lang, ';q=');
            if ($i !== false) {
                $q = (float)trim(substr($lang, $i + 3));
                $lang = trim(substr($lang, 0, $i));
            }
            $i = strpos($lang, '-');
            $lang = $i === false ? $lang : trim(substr($lang, 0, $i));
            if ($lang === '') {
                continue;
            }
            $qualities[] = $q;
            $languages[] = $lang;
        }
        array_multisort($qualities, SORT_DESC, $languages);
        $languages = array_unique($languages);
        return $languages;
    }

    protected function getCodeFromCookies($groupName)
    {
        $code = false;
        if (isset($_COOKIE['cl_' . $groupName])) {
            $code = $_COOKIE['cl_' . $groupName];
        }
        return $code;
    }

    protected function getCodeFromSession($groupName)
    {
        $code = false;
        if ($this->sessionStorageEnabled) {
            $code = $this->serverSessionManager->get('currentLanguage' . $groupName);
        }
        return $code;
    }

    protected function getCodeFromURI()
    {
        $controller = $this->getService(controller::class);
        $code = false;

        if (count($controller->requestedPath) > 0) {
            $code = reset($controller->requestedPath);
        }

        return $code;
    }

    public function getFirstAvailableCode($groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic');
        if ($language = $this->getFirstAvailableLanguage($groupName)) {
            return $language->iso6393;
        }
        return false;
    }

    protected function getFirstAvailableLanguage($groupName)
    {
        $result = null;
        $languagesList = $this->getLanguagesList($groupName) ?: [];
        foreach ($languagesList as &$language) {
            if (!$language->hidden) {
                $result = $language;
                break;
            }
        }
        if (!$result && $languagesList) {
            $result = $languagesList[0];
        }
        return $result;
    }

    protected function getCodeFromConfig()
    {
        return $this->getService(ConfigManager::class)->get('languages.default');
    }

    public function checkLanguageCode($code, $groupName, $checkHidden = true)
    {
        $result = false;
        if ($code) {
            $map = $this->getLanguagesCiMap($groupName);
            $code = strtolower($code);
            $result = isset($map[$code]) && ($checkHidden || !$map[$code]->hidden)
                ? $map[$code] : false;
        }
        return $result;
    }

    public function setCurrentLanguageCode($code, $groupName = null)
    {
        $publicGroup = $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
        $groupName = $groupName ?: $publicGroup;
        if (!isset($this->currentLanguageInfo[$groupName]) || $this->currentLanguageInfo[$groupName]->iso6393 != $code) {
            if ($info = $this->checkLanguageCode($code, $groupName)) {
                $this->currentLanguageInfo[$groupName] = $info;
                if ($this->sessionStorageEnabled) {
                    $this->serverSessionManager->set('currentLanguage' . $groupName, $code);
                }
                if (!headers_sent()) {
                    setcookie('cl_' . $groupName, $code, time() + 30 * 24 * 60 * 60, '/');
                }
                if ($groupName === $publicGroup && $this->getCodeFromURI() === $code) {
                    $this->saveLanguagePreference($code);
                }
            }
        }
    }

    protected function saveLanguagePreference(string $code): void
    {
        try {
            $this->getService(\ZxArt\UserPreferences\UserPreferencesService::class)
                ->setPreference(\ZxArt\UserPreferences\Domain\PreferenceCode::LANGUAGE->value, $code);
        } catch (\Throwable $e) {
            $this->logError($e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function getLanguagesMap($groupName = null)
    {
        $groupName = $groupName ?: $this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic');
        if (!isset($this->map[$groupName])) {
            $this->map[$groupName] = [];
            foreach ($this->getLanguagesList($groupName) as $language) {
                $this->map[$groupName][$language->iso6393] = $language;
            }
        }
        return $this->map[$groupName];
    }

    protected function getLanguagesCiMap($groupName = null)
    {
        // TODO: replace getLanguagesMap?
        $groupName = $groupName ?: $this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic');
        if (!isset($this->ciMap[$groupName])) {
            $this->ciMap[$groupName] = [];
            foreach ($this->getLanguagesList($groupName) as $language) {
                $this->ciMap[$groupName][strtolower($language->iso6393)] = $language;
            }
        }
        return $this->ciMap[$groupName];
    }

    protected function sortLanguages(array &$languages)
    {
        if ($languages) {
            $languagesIds = [];
            foreach ($languages as $language) {
                $languagesIds[] = $language->id;
            }
            $positionsMap = [];
            $collection = persistableCollection::getInstance('structure_links');
            $conditions = [
                [
                    'column' => 'childStructureId',
                    'action' => 'IN',
                    'argument' => $languagesIds,
                ],
            ];
            if ($rows = $collection->conditionalLoad([
                'childStructureId',
                'position',
            ], $conditions)
            ) {
                foreach ($rows as &$row) {
                    $positionsMap[$row['childStructureId']] = $row['position'];
                }
            }
            $positions = [];
            foreach ($languages as $language) {
                $positions[] = isset($positionsMap[$language->id]) ? $positionsMap[$language->id] : 0;
            }
            array_multisort($positions, SORT_ASC, $languages);
        }
    }

    public function getCurrentLanguageElement($groupName = null)
    {
        $structureManager = $this->getService('structureManager');
        return $structureManager->getElementById($this->getCurrentLanguageId($groupName));
    }

    public function getLanguagesIdsMap(?string $groupName = null)
    {
        $result = [];
        $groupName = $groupName ?: $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
        foreach ($this->getLanguagesList($groupName) as $language) {
            $result[$language->iso6393] = $language->id;
        }
        return $result;
    }
}