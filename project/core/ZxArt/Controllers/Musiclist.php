<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controllerApplication;
use ErrorLog;
use LanguagesManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\MusicList\MusicListService;
use ZxArt\Shared\SortingParams;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Rest\TuneRestDto;

class Musiclist extends controllerApplication
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
        $elementId = (int)($this->getParameter('elementId') ?? 0);
        $compoType = $this->getParameter('compoType') ?: null;
        $limit = $this->getParameter('limit') !== null ? (int)$this->getParameter('limit') : null;
        $start = (int)($this->getParameter('start') ?? 0);
        $sortingRaw = $this->getParameter('sorting') ?: 'title,asc';

        try {
            if ($elementId <= 0) {
                $this->assignError('elementId is required', 400);
            } elseif ($limit !== null) {
                $sorting = SortingParams::fromRequest($sortingRaw, MusicListService::ALLOWED_SORT_COLUMNS);
                $service = $this->getService(MusicListService::class);
                $result = $service->getPagedByLinkedElement($elementId, 'tagLink', $sorting, $start, $limit);
                $mapper = new ObjectMapper();
                $this->assignSuccess([
                    'total' => $result['total'],
                    'items' => array_map(
                        fn(TuneDto $dto) => $mapper->map($dto, TuneRestDto::class),
                        $result['items']
                    ),
                ]);
            } else {
                $service = $this->getService(MusicListService::class);
                $dtos = $service->getTunes($elementId, $compoType);
                $mapper = new ObjectMapper();
                $restDtos = array_map(
                    fn(TuneDto $dto) => $mapper->map($dto, TuneRestDto::class),
                    $dtos
                );
                $this->assignSuccess($restDtos);
            }
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Musiclist::execute',
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
