<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Parties\Exception\PartyDataException;
use ZxArt\Parties\Rest\PartyDataResultRestDto;
use ZxArt\Parties\Services\PartyDataService;

/**
 * Changes to one party: POST `/party-data/?id=&action=` with the action one of
 * `delete`. Answers the id of the element the change produced.
 *
 * @psalm-api
 */
final class PartyData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly PartyDataService $partyDataService,
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
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                throw new PartyDataException('Method not allowed', 405);
            }
            $partyId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'delete' => $this->partyDataService->delete($partyId),
                default => throw new PartyDataException('Unsupported party action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, PartyDataResultRestDto::class));
        } catch (PartyDataException $exception) {
            $this->logThrowable('PartyData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('PartyData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    private function assignError(string $message, int $statusCode): void
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
