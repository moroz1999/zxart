<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

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
        string $applicationName,
        private readonly ObjectMapper $objectMapper,
        private readonly RatingsService $ratingsService,
    ) {
        parent::__construct($controller, $applicationName);
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
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Unknown action');
        }
        $this->renderer->display();
    }

    protected function handleGet(): void
    {
        $elementId = (int)$this->getParameter('id');
        if (!$elementId) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'No ID provided');
            return;
        }

        try {
            $listDto = $this->ratingsService->getElementRatings($elementId);
            $restDto = $this->objectMapper->map($listDto, ElementRatingsListRestDto::class);

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDto);
        } catch (Throwable) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }

    protected function handleList(): void
    {
        $limit = (int)$this->getParameter('limit') ?: 20;

        try {
            $listDto = $this->ratingsService->getRecentRatings($limit);
            $restDto = $this->objectMapper->map($listDto, RecentRatingsListRestDto::class);

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDto);
        } catch (Throwable) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }
    public function getUrlName()
    {
        return '';
    }
}
