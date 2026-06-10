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
use ZxArt\Parties\Services\PartyOverviewService;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\Rest\ProdRestDto;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Rest\TuneRestDto;

class PartyOverview extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly PartyOverviewService $partyOverviewService,
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
            $overview = $this->partyOverviewService->getOverview($partyId);
            $this->renderer->assign('body', [
                'prods' => array_map(
                    fn(ProdDto $dto) => $this->objectMapper->map($dto, ProdRestDto::class),
                    $overview->prods,
                ),
                'pictures' => array_map(
                    fn(PictureDto $dto) => $this->objectMapper->map($dto, PictureRestDto::class),
                    $overview->pictures,
                ),
                'tunes' => array_map(
                    fn(TuneDto $dto) => $this->objectMapper->map($dto, TuneRestDto::class),
                    $overview->tunes,
                ),
            ]);
        } catch (ProdDetailsException $e) {
            $this->logThrowable('PartyOverview::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PartyOverview::execute', $e);
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
