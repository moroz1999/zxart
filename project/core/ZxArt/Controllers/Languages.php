<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use ConfigManager;
use controller;
use LanguageLinksService;
use LanguagesManager;
use Monolog\Logger;
use structureManager;
use Throwable;

class Languages extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ConfigManager $configManager,
        private readonly LanguagesManager $languagesManager,
        private readonly structureManager $structureManager,
        private readonly LanguageLinksService $languageLinksService,
    ) {
        parent::__construct($controller, $logger);
    }

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
            $this->logThrowable('Languages::execute', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }

    private function buildLanguageList(): array
    {
        $groupName = $this->configManager->get('main.rootMarkerPublic');

        $languages = $this->languagesManager->getLanguagesList($groupName);

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
            $activeLanguageCode = $this->languagesManager->getCurrentLanguageCode($groupName);
        }

        $languageLinks = [];
        if (!empty($pathSegments)) {
            $this->structureManager->setRequestedPath($pathSegments);
            $currentElement = $this->structureManager->getCurrentElement();
            if ($currentElement !== null) {
                $languageLinks = $this->languageLinksService->getLanguageLinks($currentElement);
            }
        }

        $flagMap = ['en' => '🇬🇧', 'ru' => '🇷🇺', 'es' => '🇪🇸'];

        $result = [];
        foreach ($languages as $language) {
            $url = $languageLinks[$language->iso6391] ?? '/' . $language->iso6393 . '/';

            $result[] = [
                'code' => $language->iso6393,
                'title' => $language->title,
                'flag' => $flagMap[$language->iso6391] ?? '',
                'url' => $url,
                'active' => $language->iso6393 === $activeLanguageCode,
                'homeUrl' => '/' . $language->iso6393 . '/',
            ];
        }

        return $result;
    }

    public function getUrlName(): string
    {
        return '';
    }
}
