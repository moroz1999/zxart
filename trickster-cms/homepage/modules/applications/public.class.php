<?php

use ZxArt\NgAssetsProvider;
use ZxArt\PageMetadata\PageMetadataDto;
use ZxArt\PageMetadata\PageMetadataService;
use ZxArt\Shared\StructureType;
use ZxArt\Spa\SpaRouter;
use ZxArt\Tags\TagSection;
use ZxArt\Urls\EntityUrlResolver;
use ZxArt\UserPreferences\CurrentThemeProvider;

class publicApplication extends controllerApplication implements ThemeCodeProviderInterface
{
    use DbLoggableApplication;

    protected $applicationName = 'public';
    /** @var DesignTheme */
    protected $currentTheme;
    protected $themeCode = '';
    protected $protocolRedirection = true;
    public $rendererName = 'smarty';
    /** @var ConfigManager */
    protected $configManager;

    #[Override]
    public function initialize()
    {
        $this->configManager = $this->getService(ConfigManager::class);
        $this->themeCode = $this->configManager->get('main.publicTheme');
        $this->startSession('public', $this->configManager->get('main.publicSessionLifeTime'));
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller)
    {
        $this->startDbLogging();
        $this->checkBotUAs();

        $cache = $this->getService(Cache::class);
        $cache->enable();

        $designThemesManager = $this->getService(DesignThemesManager::class);
        $designThemesManager->setCurrentThemeCode($this->getThemeCode());
        $this->currentTheme = $designThemesManager->getCurrentTheme();

        $requestUri = (string)$this->getService(requestHeadersManager::class)->getUri();
        if ($this->getService(SpaRouter::class)->isSpaRequest($requestUri)) {
            $pageExists = $this->getService(PageMetadataService::class)->pathExists($requestUri);
            if (!$pageExists) {
                $this->log404Error($requestUri);
                $this->deleteOld404();
                CmsHttpResponse::getInstance()->setStatusCode('404');
            }
            $this->renderSpa($requestUri, !$pageExists);
            $this->saveDbLog();
            return null;
        }

        $this->redirectLegacyRequest($controller, $requestUri);
        $this->handle404($requestUri);
        $this->saveDbLog();

        return null;
    }

    private function redirectLegacyRequest(controller $controller, string $requestUri): void
    {
        if ($controller->requestedFile) {
            return;
        }

        $structureManager = $this->getService(structureManager::class);
        $currentElement = $structureManager->getCurrentElement();
        if (!$currentElement instanceof structureElement) {
            return;
        }

        $redirectionManager = $this->getService(RedirectionManager::class);
        if ($this->protocolRedirection === true) {
            $redirectionManager->checkProtocolRedirection();
        }
        $redirectionManager->checkDomainRedirection();

        if ($controller->getParameter('action')) {
            return;
        }

        $query = parse_url($requestUri, PHP_URL_QUERY);
        $query = is_string($query) && $query !== '' ? $query : null;
        if ($this->redirectLanguageRoot($controller, $structureManager, $currentElement, $query)) {
            return;
        }

        $spaUrl = null;
        if ($currentElement instanceof tagElement) {
            $section = TagSection::tryFrom((string)$this->getService(SectionLogics::class)->getArtItemsType());
            $spaUrl = $section?->tagPath($currentElement->getId());
        }
        $spaUrl ??= $this->getService(EntityUrlResolver::class)->resolve($currentElement);
        if ($spaUrl === null) {
            return;
        }

        if ($query !== null) {
            $querySeparator = str_contains($spaUrl, '?') ? '&' : '?';
            $spaUrl .= $querySeparator . $query;
        }
        $controller->redirect($controller->baseURL . ltrim($spaUrl, '/'), '301');
    }

    private function redirectLanguageRoot(
        controller $controller,
        structureManager $structureManager,
        structureElement $currentElement,
        ?string $query,
    ): bool {
        $languagesManager = $this->getService(LanguagesManager::class);
        $currentLanguageId = (int)$languagesManager->getCurrentLanguageId();
        $currentLanguageElement = $currentLanguageId > 0
            ? $structureManager->getElementById($currentLanguageId)
            : null;
        $firstPageElement = $currentLanguageElement instanceof languageElement
            ? $currentLanguageElement->getFirstPageElement()
            : null;
        $isLanguageRoot = $currentElement->structureType === StructureType::Root->value
            || $currentElement->structureType === StructureType::Language->value;
        $isFirstPage = $firstPageElement instanceof structureElement
            && (int)$firstPageElement->id === (int)$currentElement->id;
        if (!$isLanguageRoot && !$isFirstPage) {
            return false;
        }

        $homeUrl = $controller->baseURL;
        if ($query !== null) {
            $homeUrl .= '?' . $query;
        }
        $controller->redirect($homeUrl, '301');
        return true;
    }

    private function handle404(string $requestUri): void
    {
        if ($this->checkBotWords($requestUri) !== null) {
            $this->renderer->fileNotFound();
            exit;
        }

        $redirectionManager = $this->getService(RedirectionManager::class);
        if ($redirectionManager->checkRedirectionUrl($requestUri)) {
            return;
        }

        $this->log404Error($requestUri);
        $this->deleteOld404();
        CmsHttpResponse::getInstance()->setStatusCode('404');
        $this->renderSpa($requestUri, true);
    }

    private function renderSpa(string $requestUri, bool $forceNoIndex = false): void
    {
        $ngAssetsProvider = $this->getService(NgAssetsProvider::class);
        $languagesManager = $this->getService(LanguagesManager::class);
        $currentLanguage = $languagesManager->getCurrentLanguage();
        $settingsManager = $this->getService(settingsManager::class);
        $themeColor = $settingsManager->getSetting('primary_color');
        $themeColor = $themeColor ?: $this->configManager->get('colors.primary_color');
        $metadata = $this->getService(PageMetadataService::class)->getForPath($requestUri);
        if ($forceNoIndex) {
            $metadata = new PageMetadataDto(
                $metadata->title,
                $metadata->description,
                true,
                $metadata->openGraph,
                $metadata->twitter,
                $metadata->languageLinks,
                $metadata->structuredData,
            );
        }

        $this->renderer->assign('ngScriptUrls', $ngAssetsProvider->getScriptUrls());
        $this->renderer->assign('ngStyleUrls', $ngAssetsProvider->getStyleUrls());
        $this->renderer->assign('currentThemeClass', $this->getService(CurrentThemeProvider::class)->getThemeClass());
        $this->renderer->assign('currentLanguage', $currentLanguage);
        $this->renderer->assign('themeColor', $themeColor);
        $this->renderer->assign('pageMetadata', $metadata);
        $this->renderer->assign('structuredDataJson', $this->encodeStructuredData($metadata));
        $this->renderer->assign('controller', controller::getInstance());
        $this->renderer->setCacheControl('no-cache');
        $this->renderer->template = $this->currentTheme->template('index.spa.tpl');
        $this->renderer->display();
    }

    private function encodeStructuredData(PageMetadataDto $metadata): ?string
    {
        if ($metadata->structuredData === null) {
            return null;
        }

        return json_encode(
            $metadata->structuredData,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
        );
    }

    private function checkBotWords(string $errorUrl): ?string
    {
        $botWordsConfig = $this->configManager->getConfig('botrequests');
        if ($botWordsConfig === null) {
            return null;
        }

        $botWords = (array)$botWordsConfig->getData();
        foreach ($botWords as $botWord => $value) {
            if (is_string($botWord) && stripos($errorUrl, $botWord) !== false) {
                return $botWord;
            }
        }

        return null;
    }

    private function log404Error(string $errorUrl): void
    {
        $referer = (string)($this->getService(requestHeadersManager::class)->getReferer() ?: '');
        $database = $this->getService('db');
        $row = $database->table('404_log')->where('errorUrl', '=', $errorUrl)->take(1)->get();
        $record = array_shift($row);
        if ($record) {
            $database->table('404_log')->whereId($record['id'])->increment('count', 1, [
                'date' => time(),
                'httpReferer' => $referer,
            ]);
            return;
        }

        $database->table('404_log')->insert([
            'errorUrl' => $errorUrl,
            'count' => 1,
            'httpReferer' => $referer,
            'date' => time(),
        ]);
    }

    private function deleteOld404(): void
    {
        $this->getService('db')->table('404_log')
            ->where('date', '<', time() - 3600 * 24 * 7)
            ->where('redirectionId', '=', 0)
            ->delete();
    }

    #[Override]
    public function getUrlName()
    {
        return '';
    }

    #[Override]
    public function getThemeCode()
    {
        return $this->themeCode;
    }

    public function getCurrentTheme(): DesignTheme
    {
        return $this->currentTheme;
    }

    public function checkBotUAs(): void
    {
        $bots = ['IZaBEE', 'DotBot', 'SemrushBot', 'AhrefsBot', 'BLEXBot', 'ubermetrics', 'Cliqzbot'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($userAgent === '') {
            return;
        }

        foreach ($bots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                exit;
            }
        }
    }
}
