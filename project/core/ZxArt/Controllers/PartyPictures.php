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
use ZxArt\Parties\Services\PartyPicturesService;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Prods\Exception\ProdDetailsException;

class PartyPictures extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly PartyPicturesService $partyPicturesService,
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
            $dtos = $this->partyPicturesService->getPictures($partyId, $compoType);
            $this->renderer->assign('body', array_map(
                fn(PictureDto $dto) => $this->objectMapper->map($dto, PictureRestDto::class),
                $dtos,
            ));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('PartyPictures::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PartyPictures::execute', $e);
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
