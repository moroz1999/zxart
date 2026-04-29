<?php

class RedirectionManager implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    public function redirectToElement($elementId, $languageCode = '')
    {
        $languagesManager = $this->getService(LanguagesManager::class);
        $languageId = null;
        if ($languageCode) {
            if ($language = $languagesManager->getLanguageByCode($languageCode)) {
                $languageId = $language->id;
            }
        }
        $configManager = $this->getService(ConfigManager::class);
        $structureManager = $this->getService('publicStructureManager');
        if ($languageCode) {
            $structureManager->setRequestedPath([$languageCode]);
        }

        //if language was provided, then try to get the element through requested language
        if ($languageId) {
            if ($element = $structureManager->getElementById($elementId, $languageId)) {
                $this->redirect($element->URL);
                return true;
            }
        }
        //try to get the element through any language
        if ($element = $structureManager->getElementById($elementId, $structureManager->getRootElementId())) {
            $this->redirect($element->URL);
            return true;
        }

        //just redirect to current language
        if ($element = $structureManager->getElementById($languageId, $structureManager->getRootElementId())) {
            $this->redirect($element->URL);
            return true;
        }
        return false;
    }

    public function switchLanguage($newLanguageCode, $referrerElementId = 0, $application = '')
    {
        /**
         * @var uriSwitchLogics $uriSwitchLogics
         */
        $uriSwitchLogics = $this->getService(uriSwitchLogics::class);
        $uriSwitchLogics->setLanguageCode($newLanguageCode);
        $uriSwitchLogics->setApplication($application);
        $httpStatus = '302';
        $url = $uriSwitchLogics->findForeignRelativeUrl($referrerElementId, $httpStatus);
        $this->redirect($url, $httpStatus);
    }

    public function redirectToPublic($uri, $newLanguage = null)
    {
        $uriSwitchLogics = $this->getService(uriSwitchLogics::class);
        $uriSwitchLogics->setCurrentUri($uri);

        if (!is_null($newLanguage)) {
            $uriSwitchLogics->setLanguage($newLanguage);
        }

        $result = $uriSwitchLogics->getDesktopUri();
        $this->redirect($result, '302');
    }

    public function redirectToDesktop($uri = null)
    {
        $uriSwitchLogics = $this->getService(uriSwitchLogics::class);
        $uriSwitchLogics->setCurrentMobileUri($uri);

        $result = $uriSwitchLogics->getDesktopUri();
        $this->redirect($result, '302');
    }

    public function redirect($uri, $status = 302)
    {
        if ($status == 301) {
            header("HTTP/1.1 301 Moved Permanently");
        } elseif ($status == 302) {
            header("HTTP/1.1 302 Found");
        } elseif ($status == 303) {
            header("HTTP/1.1 303 See Other");
        } elseif ($status == 404) {
            header("HTTP/1.0 404 Not Found");
        }
        header('Location: ' . $uri);
        exit;
    }

    public function checkRedirectionUrl($errorUrl)
    {
        if ($redirectUrl = $this->getRedirectionUrl($errorUrl)) {
            $this->redirect($redirectUrl, '301');
        } elseif ($redirectUrl = $this->getBestGuessRedirectionUrl($errorUrl)) {
            $this->redirect($redirectUrl, '301');
        }
        return false;
    }

    public function getRedirectionUrl($errorUrl)
    {
        $redirectUrl = "";
        /**
         * @var \Illuminate\Database\MySqlConnection $db
         */
        $db = $this->getService('db');
        $query = $db->table('module_redirect')
            ->where('sourceUrl', '=', $errorUrl)
            ->where('partialMatch', '=', 0)
            ->limit(1);

        $relevantRecord = $query->first();

        if (!$relevantRecord) {
            $query = $db->table('module_redirect')
                ->whereRaw('? LIKE CONCAT("%", sourceUrl, "%")', [$errorUrl])
                ->where('partialMatch', '=', 1)
                ->limit(1);
            $relevantRecord = $query->first();
        }

        if ($relevantRecord) {
            if ($relevantRecord["destinationElementId"]) {
                $structureManager = $this->getService('publicStructureManager');

                if ($redirectElement = $structureManager->getElementById($relevantRecord["destinationElementId"])) {
                    $redirectUrl = $redirectElement->URL;
                }
            } elseif ($relevantRecord["destinationUrl"]) {
                if ($relevantRecord['partialMatch']) {
                    $redirectUrl = str_ireplace($relevantRecord["sourceUrl"], $relevantRecord["destinationUrl"],
                        $errorUrl);
                } else {
                    $redirectUrl = $relevantRecord["destinationUrl"];
                }
            }
        }
        return $redirectUrl;
    }

    public function checkProtocolRedirection()
    {
        $configProtocol = $this->getService(ConfigManager::class)->get('main.protocol');

        if ($configProtocol) {
            $controller = $this->getService(controller::class);
            $currentProtocol = $controller->getProtocol();
            if ($currentProtocol != $configProtocol) {
                $this->redirect($controller->fullURL, 301);
            }
        }

        return false;
    }

    public function checkDomainRedirection()
    {
        if ($domainRedirections = $this->getService(ConfigManager::class)->get('domains.redirections')) {
            $controller = $this->getService(controller::class);
            foreach ($domainRedirections as $domain => $url) {
                if (stripos($controller->domainName, $domain) !== false) {
                    $this->redirect($url, 301);
                }
            }
        }
        return false;
    }

    protected function getBestGuessRedirectionUrl($errorUrl)
    {
        $configManager = $this->getService(ConfigManager::class);
        $types = $configManager->get('main.bestGuesssTypes');
        if (!$types) {
            return false;
        }
        if ($urlInfo = parse_url($errorUrl)) {
            if (!empty($urlInfo['path'])) {
                if ($urlParts = explode('/', $urlInfo['path'])) {
                    foreach ($urlParts as $key => $part) {
                        if (trim($part) === '') {
                            unset($urlParts[$key]);
                        }
                    }
                    if (count($urlParts) > 1) {
                        $db = $this->getService('db');
                        $structureManager = $this->getService('structureManager');
                        if ($rows = $db->table('structure_elements')
                            ->select('id')
                            ->where('structureName', '=', trim(last($urlParts)))
                            ->whereIn('structureType', $types)
                            ->limit(1)
                            ->get()
                        ) {
                            $row = reset($rows);
                            if ($element = $structureManager->getElementById($row['id'])) {
                                return $element->URL;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}