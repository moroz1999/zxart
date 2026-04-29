<?php

class uriSwitchLogics implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    protected $languageCode = '';
    protected $application = '';
    /**
     * @var controller
     */
    protected $controller;
    /**
     * @var LanguagesManager
     */
    protected $languagesManager;
    /**
     * @var structureManager
     */
    protected $structureManager;
    /**
     * @var linksManager
     */
    protected $linksManager;

    public function setController(?controller $controller): void
    {
        $this->controller = $controller;
    }

    public function setLanguagesManager(?LanguagesManager $languagesManager): void
    {
        $this->languagesManager = $languagesManager;
    }

    public function setStructureManager(?structureManager $structureManager): void
    {
        $this->structureManager = $structureManager;
    }

    public function setLinksManager(?linksManager $linksManager): void
    {
        $this->linksManager = $linksManager;
    }

    public function __construct()
    {
    }

    public function getMobileUrlBase()
    {
        return '//' . $this->controller->domainURL . 'mobile/';
    }

    public function findForeignRelativeUrl($elementId, &$httpStatus)
    {
        $url = '';
        if ($this->languageCode) {
            $marker = $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
            $this->languagesManager->setCurrentLanguageCode($this->languageCode, $marker);
            $targetLanguageId = $this->languagesManager->getCurrentLanguageId();

            $baseUrl = $this->controller->baseURL;
            if ($this->application && $this->application !== 'public') {
                $baseUrl .= $this->application . '/';
            }
            $this->structureManager->setRootUrl($baseUrl);
            $this->structureManager->setRequestedPath([$this->languageCode]);

            if ($this->structureManager->checkElementInParent($elementId, $targetLanguageId)) {
                $element = $this->structureManager->getElementById($elementId, $targetLanguageId);
            } else {
                $element = $this->findForeignConnectedElement($elementId, $targetLanguageId);
            }

            if ($element) {
                $httpStatus = "301";
                $url = $element->getUrl();
            }

            if (!$url) {
                $httpStatus = "302";
                $url = $baseUrl . $this->languageCode . '/';
            }
        }
        return $url;
    }

    protected function findForeignConnectedElement($id, $languageId)
    {
        $relative = false;
        if ($connectedIds = $this->linksManager->getConnectedIdList($id, 'foreignRelative', 'parent')) {
            foreach ($connectedIds as $connectedId) {
                if ($element = $this->structureManager->getElementById($connectedId, $languageId)) {
                    $relative = $element;
                    break;
                }
            }
        }
        return $relative;
    }

    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
    }

    public function setApplication($application)
    {
        $this->application = $application;
    }
}