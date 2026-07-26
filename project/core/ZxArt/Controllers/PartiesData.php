<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Parties\Dto\PartyDto;
use ZxArt\Parties\Rest\PartyRestDto;
use ZxArt\Parties\Services\PartiesService;

/**
 * Parties list endpoint for the SPA `/parties` page (`/parties-data/`). Returns
 * the 20 recent parties or the parties for one requested year. Named
 * *PartiesData* so `/parties` stays free for the SPA page.
 */
class PartiesData extends LoggedControllerApplication
{
    private const int RECENT_LIMIT = 20;

    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly PartiesService $partiesService,
        private readonly ObjectMapper $objectMapper,
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
            $this->renderer->assign('body', [
                'parties' => array_map(
                    fn(PartyDto $party): PartyRestDto => $this->objectMapper->map($party, PartyRestDto::class),
                    $this->buildParties(),
                ),
            ]);
        } catch (Throwable $e) {
            $this->logThrowable('PartiesData::execute', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }

    /** @return list<\ZxArt\Parties\Dto\PartyDto> */
    private function buildParties(): array
    {
        $year = (int)($this->getParameter('year') ?: 0);
        if ($year > 0) {
            return $this->partiesService->getByYear($year);
        }

        return $this->partiesService->getRecent(self::RECENT_LIMIT);
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
