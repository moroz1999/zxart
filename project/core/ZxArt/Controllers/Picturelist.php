<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use persistableCollection;
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
                    $kind = $this->getParameter('kind') ?: '';
                    if ($kind !== '') {
                        $items = match ($kind) {
                            'author' => $this->pictureListService->getRelatedByAuthors($pictureId),
                            'tags' => $this->pictureListService->getRelatedByTags($pictureId),
                            'prod' => $this->pictureListService->getRelatedFromGame($pictureId),
                            default => [],
                        };
                        $this->assignSuccess([
                            'items' => array_map(
                                fn(PictureDto $dto) => $this->objectMapper->map($dto, PictureRestDto::class),
                                $items
                            ),
                        ]);
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
                }
            } else {
                // No wrapper element id from the SPA: resolve the catalogue root by type.
                $elementId = $elementId > 0 ? $elementId : $this->resolveRootId('picturesCatalogue');
                if ($elementId <= 0) {
                    $this->assignError('picturesCatalogue root not found');
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

    /**
     * Resolves the unique collection root element id by structure type. The
     * catalogue roots live outside the public language tree, so the id is read
     * straight from the elements table instead of walking the navigable tree.
     */
    private function resolveRootId(string $structureType): int
    {
        /** @var persistableCollection $collection */
        $collection = persistableCollection::getInstance('structure_elements');
        /** @var list<array{id: scalar}> $rows */
        $rows = $collection->conditionalLoad(
            ['id'],
            [['column' => 'structureType', 'action' => '=', 'argument' => $structureType]]
        );
        foreach ($rows as $row) {
            return (int)$row['id'];
        }
        return 0;
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
