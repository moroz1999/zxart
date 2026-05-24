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
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Pictures\Services\PicturesService;

class Pictures extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly PicturesService $picturesService,
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
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $action = $this->getParameter('action') ?? '';

        if ($method === 'GET' && $action === 'picturesByElement') {
            $this->handlePicturesByElement();
        } else {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'Unknown action']);
        }

        $this->renderer->display();
    }

    private function handlePicturesByElement(): void
    {
        $elementId = (int)($this->getParameter('elementId') ?? 0);
        if ($elementId <= 0) {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'elementId is required']);
            return;
        }

        $limit = (int)($this->getParameter('limit') ?? 0);

        try {
            if ($limit > 0) {
                $start = (int)($this->getParameter('start') ?? 0);
                $sortColumn = (string)($this->getParameter('sortColumn') ?? 'votes');
                $sortDir = (string)($this->getParameter('sortDir') ?? 'desc');
                $typeFilter = (string)($this->getParameter('format') ?? '');
                $result = $this->picturesService->getByAuthorPaged($elementId, $start, $limit, $sortColumn, $sortDir, $typeFilter);
                $this->renderer->assign('body', [
                    'items' => array_map(fn(PictureDto $dto) => $this->objectMapper->map($dto, PictureRestDto::class), $result['items']),
                    'total' => $result['total'],
                    'availableFormats' => $result['availableFormats'],
                ]);
            } else {
                $dtos = $this->picturesService->getByAuthor($elementId);
                $this->renderer->assign('body', array_map(
                    fn(PictureDto $dto) => $this->objectMapper->map($dto, PictureRestDto::class),
                    $dtos
                ));
            }
        } catch (Throwable $e) {
            $this->logThrowable('Pictures::picturesByElement', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }

    public function getUrlName()
    {
        return '';
    }
}
