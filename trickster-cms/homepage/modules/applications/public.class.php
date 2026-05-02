<?php

use App\Users\CurrentUserService;
use ZxArt\NgAssetsProvider;
use ZxArt\UserPreferences\CurrentThemeProvider;

class publicApplication extends controllerApplication implements ThemeCodeProviderInterface
{
    use JsTranslationsTrait;
    use DbLoggableApplication;

    protected $applicationName = 'public';
    /**
     * @var DesignTheme
     */
    protected $currentTheme;
    protected $themeCode = '';
    protected $requestsLogging = false;
    protected $protocolRedirection = true;
    public $rendererName = 'smarty';
    /**
     * @var ConfigManager
     */
    protected $configManager;

    public function initialize()
    {
        $this->configManager = $this->getService(ConfigManager::class);
        $this->themeCode = $this->configManager->get('main.publicTheme');
        $this->startSession('public', $this->configManager->get('main.publicSessionLifeTime'));
        $this->createRenderer();
    }

    /**
     * @param controller $controller
     * @return mixed|void
     * @throws Exception
     */
    public function execute($controller)
    {
        $this->startDbLogging();
        $this->checkBotUAs();
        $this->logRequest();
        /**
         * @var Cache $cache
         */
        $cache = $this->getService(Cache::class);
        $cache->enable();

        $designThemesManager = $this->getService(DesignThemesManager::class);
        $designThemesManager->setCurrentThemeCode($this->getThemeCode());
        $currentTheme = $this->currentTheme = $designThemesManager->getCurrentTheme();
        $structureManager = $this->getService('structureManager');
        $this->processRequestParameters();
        $this->renderer->assign('js_translations', $this->loadJsTranslations());

        $resourcesUniterHelper = $this->getService(ResourcesUniterHelper::class);
        $resourcesUniterHelper->setCurrentThemeCode($this->getThemeCode());
        $this->renderer->assign('CSSFileName', $resourcesUniterHelper->getResourceCacheFileName('css'));

        $this->renderer->assign('controller', $controller);
        $this->renderer->assign('configManager', $this->configManager);
        /**
         * @var $settingsManager settingsManager
         */
        $settingsManager = $this->getService(settingsManager::class);
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        $this->renderer->assign('settings', $settingsManager->getSettingsList());
        $this->renderer->assign('currentUser', $user);
        $this->renderer->assign('theme', $currentTheme);

        $ngAssetsProvider = $this->getService(NgAssetsProvider::class);
        $this->renderer->assign('ngScriptUrls', $ngAssetsProvider->getScriptUrls());
        $this->renderer->assign('ngStyleUrls', $ngAssetsProvider->getStyleUrls());

        $themeColor = $settingsManager->getSetting('primary_color');
        $themeColor = $themeColor ?: $this->configManager->get('colors.primary_color');
        $this->renderer->assign('themeColor', $themeColor);
        $this->renderer->assign('applicationName', $this->applicationName);
        $this->renderer->assign('deviceType', 'desktop');

        $pageNotFound = $controller->requestedFile;
        $languagesManager = $this->getService(LanguagesManager::class);
        $visitorsManager = $this->getService(VisitorsManager::class);
        $visitorRecorded = $visitorsManager->isVisitationRecorded();
        $this->renderer->assign('newVisitor', !$visitorRecorded);

        $themeProvider = $this->getService(CurrentThemeProvider::class);
        $this->renderer->assign('currentThemeClass', $themeProvider->getThemeClass());

        if (!$pageNotFound) {
            if ($currentElement = $structureManager->getCurrentElement()) {
                /**
                 * @var $redirectionManager RedirectionManager
                 */
                $redirectionManager = $this->getService(RedirectionManager::class);
                if ($this->protocolRedirection) {
                    $redirectionManager->checkProtocolRedirection();
                }
                $redirectionManager->checkDomainRedirection();

                //check if we need to redirect user to display firstpage
                if ($currentElement->structureType === 'root' || $currentElement->structureType === 'language') {
                    if ($currentLanguageId = $languagesManager->getCurrentLanguageId()) {
                        /**
                         * @var $currentLanguageElement languageElement
                         */
                        if ($currentLanguageElement = $structureManager->getElementById($currentLanguageId)) {
                            if ($firstPageElement = $currentLanguageElement->getFirstPageElement()) {
                                $controller->restart($firstPageElement->URL);
                            } elseif ($contentElements = $currentLanguageElement->getChildrenList('content')) {
                                $firstMenu = reset($contentElements);
                                $controller->restart($firstMenu->URL);
                            } elseif ($currentElement->structureType === 'root') {
                                // site doesn't work if root is current
                                $controller->restart($currentLanguageElement->URL);
                            }
                        }
                    }
                }

                $privileges = $this->getService(privilegesManager::class)->getElementPrivileges($currentElement->id);
                $this->renderer->assign('privileges', $privileges);
                $this->renderer->assign('currentElementPrivileges', $privileges[$currentElement->structureType]);

                $breadcrumbsManager = $this->getService(breadcrumbsManager::class);
                $this->renderer->assign('breadcrumbsManager', $breadcrumbsManager);

                if ($currentElement instanceof MetadataProviderInterface) {
                    $currentMetaTitle = $currentElement->getMetaTitle();
                    $currentMetaKeywords = $currentElement->getMetaKeywords();
                    $currentMetaDescription = $currentElement->getMetaDescription();
                    $currentCanonicalUrl = $currentElement->getCanonicalUrl();
                    $currentNoIndexing = $currentElement->getMetaDenyIndex();
                } else {
                    if ($currentElement && $currentElement->title) {
                        $currentMetaTitle = $currentElement->title;
                    } else {
                        $currentMetaTitle = '';
                    }
                    $currentMetaKeywords = "";
                    $currentMetaDescription = "";
                    $currentCanonicalUrl = $currentElement->URL;
                    $currentNoIndexing = false;
                }

                if ($siteName = $this->getService(translationsManager::class)
                    ->getTranslationByName('site.name', null, false)) {
                    $currentMetaTitle .= ' - ' . $siteName;
                }

                if ($currentElement instanceof OpenGraphDataProviderInterface
                ) {
                    $this->renderer->assign('openGraphData', $currentElement->getOpenGraphData());
                }
                if ($currentElement instanceof TwitterDataProviderInterface
                ) {
                    $this->renderer->assign('twitterData', $currentElement->getTwitterData());
                }

                $languageLinksService = $this->getService(LanguageLinksService::class);
                $languageLinks = $languageLinksService->getLanguageLinks($currentElement);

                $this->renderer->assign('languageLinks', $languageLinks);
                $this->renderer->assign('jsScripts', $this->getJsScripts($currentElement));
                $this->renderer->assign('application', $this);
                $this->renderer->assign('currentMetaDescription', $currentMetaDescription);
                $this->renderer->assign('currentMetaKeywords', $currentMetaKeywords);
                $this->renderer->assign('currentMetaTitle', $currentMetaTitle);
                $this->renderer->assign('currentNoIndexing', $currentNoIndexing);
                $this->renderer->assign('currentCanonicalUrl', $currentCanonicalUrl);
                $this->renderer->assign('currentElement', $currentElement);
                $this->renderer->assign('structureManager', $structureManager);
                $this->renderer->assign('LanguagesManager', $languagesManager);
                $requestHeadersManager = $this->getService(requestHeadersManager::class);
                $this->renderer->assign('userAgent', $requestHeadersManager->getUserAgent());
                $this->renderer->setCacheControl('no-cache');
                $this->renderer->template = $currentTheme->template('index.tpl');
                $this->renderer->display();
            } else {
                $pageNotFound = true;
            }
        }
        if ($pageNotFound) {
            $this->handle404();
        }
        $this->saveDbLog();
    }

    protected function handle404()
    {
        /**
         * @var RedirectionManager $redirectionManager
         */
        $redirectionManager = $this->getService(RedirectionManager::class);
        /**
         * @var requestHeadersManager $requestHeadersManager
         */
        $requestHeadersManager = $this->getService(requestHeadersManager::class);
        $controller = controller::getInstance();
        $errorUrl = $requestHeadersManager->getUri();
        if ($word = $this->checkBotWords($errorUrl)) {
            $this->renderer->fileNotFound();
            exit;
        }

        if (!$redirectionManager->checkRedirectionUrl($errorUrl)) {
            $this->log404Error($errorUrl);
            $this->deleteOld404();
            $this->renderer->fileNotFound();
            $structureManager = $this->getService('publicStructureManager');

            $languagesManager = $this->getService(LanguagesManager::class);
            $languageId = $languagesManager->getCurrentLanguageId();
            if ($languageElement = $structureManager->getElementById($languageId)) {
                if ($currentElement = $this->getErrorPageElement()) {
                    $breadcrumbsManager = $this->getService(breadcrumbsManager::class);
                    $this->renderer->assign('breadcrumbsManager', $breadcrumbsManager);
                    $this->renderer->assign('currentElement', $currentElement);
                    $this->renderer->assign('structureManager', $structureManager);
                    $this->renderer->setCacheControl('no-cache');
                    $this->renderer->template = $this->currentTheme->template('index.tpl');
                    $this->renderer->display();
                }
            }
        }
    }

    protected function checkBotWords($errorUrl)
    {
        if ($botWords = $this->configManager->getConfig('botrequests')) {
            foreach ($botWords as $botWord => $value) {
                if (stripos($errorUrl, $botWord) !== false) {
                    return $botWord;
                }
            }
        }
        return false;
    }

    public function processRequestParameters()
    {
        $structureManager = $this->getService('structureManager');
        $controller = controller::getInstance();
        if ($controller->getParameter('type')) {
            if ($controller->getParameter('action')) {
                $requestedPath = implode('/', $controller->requestedPath) . '/';
                $structureManager->newElementParameters[$requestedPath]['action'] = $controller->getParameter('action');
                $structureManager->newElementParameters[$requestedPath]['type'] = $controller->getParameter('type');
            }
        } elseif ($controller->getParameter('action') && $controller->getParameter('id')) {
            $structureManager->customActions[$controller->getParameter('id')] = $controller->getParameter('action');
        }
    }

    protected function log404Error($errorUrl)
    {
        $requestHeadersManager = $this->getService(requestHeadersManager::class);
        $referer = $requestHeadersManager->getReferer();
        $db = $this->getService('db');
        // seek and update existing record
        $row = $db->table('404_log')->where('errorUrl', '=', $errorUrl)->take(1)->get();
        $row = array_shift($row);
        if ($row) {
            $fields = [];
            $fields['date'] = time();
            $fields['httpReferer'] = $referer ? $referer : "";
            $db->table('404_log')->whereId($row['id'])->increment('count', 1, $fields);
        } // if record not found, add new
        else {
            $record = [];
            $record['errorUrl'] = $errorUrl;
            $record['count'] = 1;
            $record['httpReferer'] = $referer ? $referer : "";
            $record['date'] = time();
            $db->table('404_log')->insert($record);
        }
    }

    protected function deleteOld404()
    {
        $db = $this->getService('db');
        $db->table('404_log')
            ->where('date', '<', time() - 3600 * 24 * 7)
            ->where('redirectionId', '=', 0)
            ->delete();
    }

    public function getErrorPageElement()
    {
        $errorPageElement = false;
        $db = $this->getService('db');
        if ($errorPageElementId = $db->table('module_errorpage')->select('id')->limit(1)->value('id')) {
            $structureManager = $this->getService('structureManager');
            $languagesManager = $this->getService(LanguagesManager::class);
            $currentLanguageElementId = $languagesManager->getCurrentLanguageId();
            $errorPageElement = $structureManager->getElementById($errorPageElementId, $currentLanguageElementId, true);
        }
        return $errorPageElement;
    }

    public function getUrlName()
    {
        return '';
    }

    public function getThemeCode()
    {
        return $this->themeCode;
    }

    /**
     * @return DesignTheme
     */
    public function getCurrentTheme()
    {
        return $this->currentTheme;
    }

    public function checkBotUAs()
    {
        $bots = [
            'IZaBEE',
            'DotBot',
            'SemrushBot',
            'AhrefsBot',
            'BLEXBot',
            'ubermetrics',
            'Cliqzbot',
        ];
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            foreach ($bots as $bot) {
                if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false) {
                    exit;
                }
            }
        }
    }

    protected function logRequest()
    {
        if ($this->requestsLogging) {
            $todayDate = date('Y-m-d');
            $pathsManager = controller::getInstance()->getPathsManager();
            $logFilePath = $pathsManager->getPath('logs') . 'access/';
            if (!is_dir($logFilePath)) {
                mkdir($logFilePath, 0775, true);
            }

            $string = date('Y.m.d H:i:s') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . $_SERVER['HTTP_USER_AGENT'] . ' ' . $_SERVER['REQUEST_URI'] . "\n";
            file_put_contents($logFilePath . $todayDate . '.txt', $string, FILE_APPEND);
        }
    }

    protected function getJsScripts($currentElement)
    {
        $controller = controller::getInstance();
        $jsScripts = [];

        $resourcesUniterHelper = $this->getService(ResourcesUniterHelper::class);
        $resourcesUniterHelper->setCurrentThemeCode($this->currentTheme->getCode());
        $jsScripts[] = $controller->baseURL . 'javascript/set:' . $this->currentTheme->getCode() . '/file:' . $resourcesUniterHelper->getResourceCacheFileName('js') . '.js';

        if ($currentElement instanceof clientScriptsProviderInterface
        ) {
            $jsScripts = array_merge($jsScripts, $currentElement->getClientScripts());
        }
        return $jsScripts;
    }
}



