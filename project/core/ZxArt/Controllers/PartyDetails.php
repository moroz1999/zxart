<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Parties\Rest\PartyCoreRestDto;
use ZxArt\Parties\Services\PartyDetailsService;
use ZxArt\Prods\Exception\ProdDetailsException;

class PartyDetails extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly PartyDetailsService $partyDetailsService,
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
            $dto = $this->partyDetailsService->getDetails($partyId);
            $this->renderer->assign('body', $this->objectMapper->map($dto, PartyCoreRestDto::class));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('PartyDetails::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PartyDetails::execute', $e);
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
