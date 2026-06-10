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
use ZxArt\Parties\Services\PartyProdsService;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\Rest\ProdRestDto;

class PartyProds extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly PartyProdsService $partyProdsService,
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
            $compoType = (string)($this->getParameter('compoType') ?? '');
            if ($compoType === '') {
                throw new ProdDetailsException('Missing required parameter: compoType', 400);
            }
            $this->renderer->assign('body', array_map(
                fn(ProdDto $dto) => $this->objectMapper->map($dto, ProdRestDto::class),
                $this->partyProdsService->getProds($partyId, $compoType),
            ));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('PartyProds::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PartyProds::execute', $e);
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
