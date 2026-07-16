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
use ZxArt\MusicList\MusicListService;
use ZxArt\Shared\SortingParams;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Rest\TuneRestDto;

class Musiclist extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly MusicListService $musicListService,
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
        $tuneId = (int)($this->getParameter('tuneId') ?? 0);
        $limit = $this->getParameter('limit') !== false ? (int)$this->getParameter('limit') : null;
        $start = (int)($this->getParameter('start') ?? 0);
        $sortingRaw = $this->getParameter('sorting') ?: 'title,asc';

        try {
            if ($action === 'related') {
                if ($tuneId <= 0) {
                    $this->assignError('tuneId is required', 400);
                } else {
                    $kind = $this->getParameter('kind') ?: '';
                    $items = match ($kind) {
                        'author' => $this->musicListService->getRelatedByAuthors($tuneId),
                        'tags' => $this->musicListService->getRelatedByTags($tuneId),
                        'tracker' => $this->musicListService->getRelatedByTracker($tuneId),
                        default => [],
                    };
                    $this->assignSuccess([
                        'items' => array_map(
                            fn(TuneDto $dto) => $this->objectMapper->map($dto, TuneRestDto::class),
                            $items
                        ),
                    ]);
                }
            } else {
                // No wrapper element id from the SPA: resolve the catalogue root by type.
                $elementId = $elementId > 0 ? $elementId : $this->resolveRootId('musicCatalogue');
                if ($elementId <= 0) {
                    $this->assignError('musicCatalogue root not found');
                } elseif ($limit !== null) {
                    $sorting = SortingParams::fromRequest($sortingRaw, MusicListService::ALLOWED_SORT_COLUMNS);
                    $result = $this->musicListService->getPagedByLinkedElement($elementId, 'structure', $sorting, $start, $limit);
                    $this->assignSuccess([
                        'total' => $result['total'],
                        'items' => array_map(
                            fn(TuneDto $dto) => $this->objectMapper->map($dto, TuneRestDto::class),
                            $result['items']
                        ),
                    ]);
                } else {
                    $dtos = $this->musicListService->getTunes($elementId, $compoType);
                    $restDtos = array_map(
                        fn(TuneDto $dto) => $this->objectMapper->map($dto, TuneRestDto::class),
                        $dtos
                    );
                    $this->assignSuccess($restDtos);
                }
            }
        } catch (Throwable $e) {
            $this->logThrowable('Musiclist::execute', $e);
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
