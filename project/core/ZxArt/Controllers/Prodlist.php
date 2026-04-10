<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use LanguagesManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\ProdList\ProdListService;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Rest\ProdRestDto;
use ZxArt\Shared\SortingParams;

class Prodlist extends LoggedControllerApplication
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
        $limit = (int)($this->getParameter('limit') ?? 50);
        $start = (int)($this->getParameter('start') ?? 0);
        $sortingRaw = $this->getParameter('sorting') ?: 'title,asc';

        try {
            if ($elementId <= 0) {
                $this->assignError('elementId is required', 400);
            } else {
                $sorting = SortingParams::fromRequest($sortingRaw, ProdListService::ALLOWED_SORT_COLUMNS);
                $service = $this->getService(ProdListService::class);
                $result = $service->getPagedByLinkedElement($elementId, 'tagLink', $sorting, $start, $limit);
                $mapper = new ObjectMapper();
                $this->assignSuccess([
                    'total' => $result['total'],
                    'items' => array_map(
                        fn(ProdDto $dto) => $mapper->map($dto, ProdRestDto::class),
                        $result['items']
                    ),
                ]);
            }
        } catch (Throwable $e) {
            $this->logThrowable('Prodlist::execute', $e);
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
