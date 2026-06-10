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
use ZxArt\Parties\Services\PartyMusicService;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Rest\TuneRestDto;

class PartyMusic extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly PartyMusicService $partyMusicService,
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
        try {
            $partyId = $this->getPartyId();
            $compoType = $this->getCompoType();
            $dtos = $this->partyMusicService->getTunes($partyId, $compoType);
            $this->renderer->assign('body', array_map(
                fn(TuneDto $dto) => $this->objectMapper->map($dto, TuneRestDto::class),
                $dtos,
            ));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('PartyMusic::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PartyMusic::execute', $e);
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

    private function getCompoType(): string
    {
        $compoType = (string)($this->getParameter('compoType') ?? '');
        if ($compoType === '') {
            throw new ProdDetailsException('Missing required parameter: compoType', 400);
        }
        return $compoType;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
