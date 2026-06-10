<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Parties\Services\PartyActivityService;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Ratings\Rest\AuthorRatingsListRestDto;

final class PartyRatings extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly PartyActivityService $activityService,
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
            $partyId = $this->getPartyId();
            $page = max(1, (int)($this->getParameter('page') ?: 1));
            $perPage = max(1, (int)($this->getParameter('perPage') ?: 20));
            $dto = $this->activityService->getRatings($partyId, $page, $perPage);
            $this->renderer->assign('body', $this->objectMapper->map($dto, AuthorRatingsListRestDto::class));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('PartyRatings::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PartyRatings::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getPartyId(): int
    {
        $partyId = (int)($this->getParameter('id') ?? 0);
        if ($partyId <= 0) {
            throw new ProdDetailsException('Missing required parameter: id', 400);
        }
        return $partyId;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
