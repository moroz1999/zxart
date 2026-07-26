<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Override;
use Throwable;
use ZxArt\Content\ContentService;

class ContentData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ContentService $contentService,
        private readonly LanguagesManager $languagesManager,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller): void
    {
        try {
            $page = (string)($this->getParameter('page') ?: '');
            $language = (string)($this->getParameter('lang') ?: $this->languagesManager->getCurrentLanguageCode());
            $html = $this->contentService->getContent($page, $language);
            if ($html === null) {
                $this->assignError('Content not found', 404);
            } else {
                $this->renderer->assign('body', ['html' => $html]);
            }
        } catch (Throwable $e) {
            $this->logThrowable('ContentData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
