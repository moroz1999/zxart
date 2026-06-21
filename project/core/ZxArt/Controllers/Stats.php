<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Override;
use structureManager;
use Throwable;
use ZxArt\Stats\Services\StatsService;

class Stats extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly StatsService $statsService,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
    }

    #[Override]
    public function execute($controller): void
    {
        $action = $this->getStringParameter('action', 'overview');

        try {
            match ($action) {
                'overview' => $this->assignSuccess($this->statsService->getOverview()),
                'soft' => $this->assignSuccess($this->statsService->getSoftSection()),
                'music' => $this->assignSuccess($this->statsService->getMusicSection()),
                'gfx' => $this->assignSuccess($this->statsService->getGfxSection()),
                'users' => $this->assignSuccess($this->statsService->getUsersSection()),
                default => $this->assignError('Unknown action', 400),
            };
        } catch (Throwable $e) {
            $this->logThrowable('Stats::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getStringParameter(string $name, string $default): string
    {
        $value = (string)($this->getParameter($name) ?: '');

        return $value !== '' ? $value : $default;
    }

    private function assignSuccess(mixed $data): void
    {
        $this->renderer->assign('body', $data);
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
