<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controllerApplication;
use LanguagesManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\PictureList\PictureListService;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Shared\SortingParams;

class Picturelist extends controllerApplication
{
    public $rendererName = 'json';

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $structureManager = $this->getService('publicStructureManager');
        $languagesManager = $this->getService(LanguagesManager::class);
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
    }

    public function execute($controller): void
    {
        $action = $this->getParameter('action') ?: '';
        $elementId = (int)($this->getParameter('elementId') ?? 0);
        $compoType = $this->getParameter('compoType') ?: null;
        $pictureId = (int)($this->getParameter('pictureId') ?? 0);
        $limit = $this->getParameter('limit') !== null ? (int)$this->getParameter('limit') : null;
        $start = (int)($this->getParameter('start') ?? 0);
        $sortingRaw = $this->getParameter('sorting') ?: 'title,asc';

        try {
            if ($action === 'related') {
                if ($pictureId <= 0) {
                    $this->assignError('pictureId is required', 400);
                } else {
                    $service = $this->getService(PictureListService::class);
                    $related = $service->getRelated($pictureId);
                    $mapper = new ObjectMapper();
                    $this->assignSuccess([
                        'type' => $related['type'],
                        'items' => array_map(
                            fn(PictureDto $dto) => $mapper->map($dto, PictureRestDto::class),
                            $related['items']
                        ),
                    ]);
                }
            } elseif ($elementId <= 0) {
                $this->assignError('elementId is required', 400);
            } elseif ($limit !== null) {
                $sorting = SortingParams::fromRequest($sortingRaw, PictureListService::ALLOWED_SORT_COLUMNS);
                $service = $this->getService(PictureListService::class);
                $result = $service->getPagedByLinkedElement($elementId, 'tagLink', $sorting, $start, $limit);
                $mapper = new ObjectMapper();
                $this->assignSuccess([
                    'total' => $result['total'],
                    'items' => array_map(
                        fn(PictureDto $dto) => $mapper->map($dto, PictureRestDto::class),
                        $result['items']
                    ),
                ]);
            } else {
                $service = $this->getService(PictureListService::class);
                $dtos = $service->getPictures($elementId, $compoType);
                $mapper = new ObjectMapper();
                $restDtos = array_map(
                    fn(PictureDto $dto) => $mapper->map($dto, PictureRestDto::class),
                    $dtos
                );
                $this->assignSuccess($restDtos);
            }
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Picturelist::execute',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
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
