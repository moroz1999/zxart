<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use structureManager;
use Throwable;
use ZxArt\Stats\Rest\StatsCategorySectionRestDto;
use ZxArt\Stats\Rest\StatsOverviewRestDto;
use ZxArt\Stats\Rest\StatsUsersSectionRestDto;
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
        private readonly ObjectMapper $objectMapper,
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
                'overview' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getOverview(), StatsOverviewRestDto::class),
                ),
                'soft' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getSoftSection(), StatsCategorySectionRestDto::class),
                ),
                'music' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getMusicSection(), StatsCategorySectionRestDto::class),
                ),
                'gfx' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getGfxSection(), StatsCategorySectionRestDto::class),
                ),
                'users' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getUsersSection(), StatsUsersSectionRestDto::class),
                ),
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
