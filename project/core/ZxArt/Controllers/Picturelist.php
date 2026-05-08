<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Symfony\Component\ObjectMapper\ObjectMapper;
use structureManager;
use Throwable;
use ZxArt\PictureList\PictureListService;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Shared\SortingParams;

class Picturelist extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly PictureListService $pictureListService,
        private readonly ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
    }

    public function execute($controller): void
    {
        $action = $this->getParameter('action') ?: '';
        $elementId = (int)($this->getParameter('elementId') ?? 0);
        $compoType = $this->getParameter('compoType') ?: null;
        $pictureId = (int)($this->getParameter('pictureId') ?? 0);
        $limit = $this->getParameter('limit') !== false ? (int)$this->getParameter('limit') : null;
        $start = (int)($this->getParameter('start') ?? 0);
        $sortingRaw = $this->getParameter('sorting') ?: 'title,asc';

        try {
            if ($action === 'related') {
                if ($pictureId <= 0) {
                    $this->assignError('pictureId is required', 400);
                } else {
                    $related = $this->pictureListService->getRelated($pictureId);
                    $this->assignSuccess([
                        'type' => $related['type'],
                        'items' => array_map(
                            fn(PictureDto $dto) => $this->objectMapper->map($dto, PictureRestDto::class),
                            $related['items']
                        ),
                    ]);
                }
            } elseif ($elementId <= 0) {
                $this->assignError('elementId is required', 400);
            } elseif ($limit !== null) {
                $sorting = SortingParams::fromRequest($sortingRaw, PictureListService::ALLOWED_SORT_COLUMNS);
                $result = $this->pictureListService->getPagedByLinkedElement($elementId, 'tagLink', $sorting, $start, $limit);
                $this->assignSuccess([
                    'total' => $result['total'],
                    'items' => array_map(
                        fn(PictureDto $dto) => $this->objectMapper->map($dto, PictureRestDto::class),
                        $result['items']
                    ),
                ]);
            } else {
                $dtos = $this->pictureListService->getPictures($elementId, $compoType);
                $restDtos = array_map(
                    fn(PictureDto $dto) => $this->objectMapper->map($dto, PictureRestDto::class),
                    $dtos
                );
                $this->assignSuccess($restDtos);
            }
        } catch (Throwable $e) {
            $this->logThrowable('Picturelist::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
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

    public function getUrlName(): string
    {
        return '';
    }
}
