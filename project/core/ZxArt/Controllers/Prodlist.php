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
use ZxArt\ProdList\ProdListService;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Rest\ProdRestDto;
use ZxArt\Shared\SortingParams;

class Prodlist extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly ProdListService $prodListService,
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
        $elementId = (int)($this->getParameter('elementId') ?? 0);
        $limit = (int)($this->getParameter('limit') ?? 50);
        $start = (int)($this->getParameter('start') ?? 0);
        $sortingRaw = $this->getParameter('sorting') ?: 'title,asc';

        try {
            if ($elementId <= 0) {
                $this->assignError('elementId is required', 400);
            } else {
                $sorting = SortingParams::fromRequest($sortingRaw, ProdListService::ALLOWED_SORT_COLUMNS);
                $result = $this->prodListService->getPagedByLinkedElement($elementId, 'tagLink', $sorting, $start, $limit);
                $this->assignSuccess([
                    'total' => $result['total'],
                    'items' => array_map(
                        fn(ProdDto $dto) => $this->objectMapper->map($dto, ProdRestDto::class),
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
