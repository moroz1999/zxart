<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use ConfigManager;
use controllerApplication;
use ErrorLog;
use LanguageLinksService;
use LanguagesManager;
use Throwable;

class Languages extends controllerApplication
{
    public $rendererName = 'json';

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        try {
            $this->renderer->assign('body', $this->buildLanguageList());
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Languages::execute',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }

    private function buildLanguageList(): array
    {
        $configManager = $this->getService(ConfigManager::class);
        $groupName = $configManager->get('main.rootMarkerPublic');

        $languagesManager = $this->getService(LanguagesManager::class);
        $languages = $languagesManager->getLanguagesList($groupName);

        $path = trim($this->getParameter('path') ?? '', '/');
        $pathSegments = $path !== '' ? explode('/', $path) : [];

        // Detect active language from path (first segment), fall back to session detection
        $activeLanguageCode = null;
        if (!empty($pathSegments)) {
            $firstSegment = strtolower($pathSegments[0]);
            foreach ($languages as $language) {
                if (strtolower($language->iso6393) === $firstSegment) {
                    $activeLanguageCode = $language->iso6393;
                    break;
                }
            }
        }
        if ($activeLanguageCode === null) {
            $activeLanguageCode = $languagesManager->getCurrentLanguageCode($groupName);
        }

        $languageLinks = [];
        if (!empty($pathSegments)) {
            $structureManager = $this->getService('structureManager');
            $structureManager->setRequestedPath($pathSegments);
            $currentElement = $structureManager->getCurrentElement();
            if ($currentElement !== null) {
                $languageLinksService = $this->getService(LanguageLinksService::class);
                $languageLinks = $languageLinksService->getLanguageLinks($currentElement);
            }
        }

        $result = [];
        foreach ($languages as $language) {
            $url = $languageLinks[$language->iso6391] ?? '/' . $language->iso6393 . '/';

            $result[] = [
                'title' => $language->title,
                'url' => $url,
                'active' => $language->iso6393 === $activeLanguageCode,
            ];
        }

        return $result;
    }

    public function getUrlName(): string
    {
        return '';
    }
}
