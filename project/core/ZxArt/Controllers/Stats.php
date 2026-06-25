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
use ZxArt\Stats\Rest\StatsCategorySummaryRestDto;
use ZxArt\Stats\Rest\StatsDailySeriesRestDto;
use ZxArt\Stats\Rest\StatsDistributionBlockRestDto;
use ZxArt\Stats\Rest\StatsDistributionsRestDto;
use ZxArt\Stats\Rest\StatsOverviewRestDto;
use ZxArt\Stats\Rest\StatsTopUsersRestDto;
use ZxArt\Stats\Rest\StatsUsersSectionRestDto;
use ZxArt\Stats\Rest\StatsYearSeriesRestDto;
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
                'soft-summary' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getSoftSummary(), StatsCategorySummaryRestDto::class),
                ),
                'music-summary' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getMusicSummary(), StatsCategorySummaryRestDto::class),
                ),
                'gfx-summary' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getGfxSummary(), StatsCategorySummaryRestDto::class),
                ),
                'soft-series' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getSoftSeries(), StatsYearSeriesRestDto::class),
                ),
                'music-series' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getMusicSeries(), StatsYearSeriesRestDto::class),
                ),
                'gfx-series' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getGfxSeries(), StatsYearSeriesRestDto::class),
                ),
                'soft-distributions' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getSoftDistributions(), StatsDistributionsRestDto::class),
                ),
                'soft-category-distribution' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getSoftCategoryDistribution(), StatsDistributionBlockRestDto::class),
                ),
                'soft-computer-distribution' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getSoftComputerDistribution(), StatsDistributionBlockRestDto::class),
                ),
                'soft-country-distribution' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getSoftCountryDistribution(), StatsDistributionBlockRestDto::class),
                ),
                'music-distributions' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getMusicDistributions(), StatsDistributionsRestDto::class),
                ),
                'music-format-distribution' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getMusicFormatDistribution(), StatsDistributionBlockRestDto::class),
                ),
                'music-country-distribution' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getMusicCountryDistribution(), StatsDistributionBlockRestDto::class),
                ),
                'gfx-distributions' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getGfxDistributions(), StatsDistributionsRestDto::class),
                ),
                'gfx-type-distribution' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getGfxTypeDistribution(), StatsDistributionBlockRestDto::class),
                ),
                'gfx-country-distribution' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getGfxCountryDistribution(), StatsDistributionBlockRestDto::class),
                ),
                'soft-daily' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getSoftDaily(), StatsDailySeriesRestDto::class),
                ),
                'music-daily' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getMusicDaily(), StatsDailySeriesRestDto::class),
                ),
                'gfx-daily' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getGfxDaily(), StatsDailySeriesRestDto::class),
                ),
                'soft-top' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getSoftTop(), StatsTopUsersRestDto::class),
                ),
                'music-top' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getMusicTop(), StatsTopUsersRestDto::class),
                ),
                'gfx-top' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getGfxTop(), StatsTopUsersRestDto::class),
                ),
                'users-voters' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getVotersTop(), StatsTopUsersRestDto::class),
                ),
                'users-comments' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getCommentersTop(), StatsTopUsersRestDto::class),
                ),
                'users-tags' => $this->assignSuccess(
                    $this->objectMapper->map($this->statsService->getTaggersTop(), StatsTopUsersRestDto::class),
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
