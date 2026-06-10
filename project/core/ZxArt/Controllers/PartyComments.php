<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Comments\CommentsListRestDto;
use ZxArt\Parties\Services\PartyActivityService;
use ZxArt\Prods\Exception\ProdDetailsException;

final class PartyComments extends LoggedControllerApplication
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
            $perPage = max(1, (int)($this->getParameter('perPage') ?: 50));
            $languageCode = $this->getLanguageCode();
            $dto = $this->activityService->getComments($partyId, $page, $perPage, $languageCode);
            $this->renderer->assign('body', $this->objectMapper->map($dto, CommentsListRestDto::class));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('PartyComments::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PartyComments::execute', $e);
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

    private function getLanguageCode(): ?string
    {
        $languageCode = (string)($this->getParameter('lang') ?? '');
        return $languageCode === '' ? null : $languageCode;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
