<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use controllerApplication;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Ratings\RatingsService;
use ZxArt\Ratings\Rest\ElementRatingsListRestDto;
use ZxArt\Ratings\Rest\RecentRatingsListRestDto;

class Ratings extends controllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        private readonly ObjectMapper $objectMapper,
        private readonly RatingsService $ratingsService,
    ) {
        parent::__construct($controller);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        $action = $this->getParameter('action');
        if (!$action) {
            $this->handleGet();
        } elseif ($action === 'list') {
            $this->handleList();
        } else {
            $this->assignError('Unknown action', 400);
        }
        $this->renderer->display();
    }

    protected function handleGet(): void
    {
        $elementId = (int)$this->getParameter('id');
        if (!$elementId) {
            $this->assignError('No ID provided', 400);
            return;
        }

        try {
            $listDto = $this->ratingsService->getElementRatings($elementId);
            $this->assignSuccess($this->objectMapper->map($listDto, ElementRatingsListRestDto::class));
        } catch (Throwable) {
            $this->assignError('Internal server error');
        }
    }

    protected function handleList(): void
    {
        $limit = (int)$this->getParameter('limit') ?: 20;

        try {
            $listDto = $this->ratingsService->getRecentRatings($limit);
            $this->assignSuccess($this->objectMapper->map($listDto, RecentRatingsListRestDto::class));
        } catch (Throwable) {
            $this->assignError('Internal server error');
        }
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

    public function getUrlName()
    {
        return '';
    }
}
