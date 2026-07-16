<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use structureManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\FileSearch\Dto\FileSearchResultDto;
use ZxArt\FileSearch\FileSearchService;
use ZxArt\FileSearch\Rest\FileSearchResultRestDto;

class FileSearchData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly FileSearchService $fileSearchService,
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
        try {
            $query = (string)($this->getParameter('q') ?: '');
            $results = $this->fileSearchService->search($query);
            $this->renderer->assign('body', [
                'items' => array_map(
                    fn(FileSearchResultDto $dto) => $this->objectMapper->map($dto, FileSearchResultRestDto::class),
                    $results
                ),
            ]);
        } catch (Throwable $e) {
            $this->logThrowable('FileSearchData::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
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
