<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use ConfigManager;
use controller;
use controllerApplication;
use ErrorLog;
use LanguagesManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Exception\TuneNotFoundException;
use ZxArt\Tunes\Rest\TuneRestDto;
use ZxArt\Tunes\Services\TunePlayService;
use ZxArt\Tunes\Services\TunesService;

class Tunes extends controllerApplication
{
    public $rendererName = 'json';
    protected TunePlayService $tunePlayService;

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $configManager = $this->getService(ConfigManager::class);
        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => controller::getInstance()->rootURL,
                'rootMarker' => $configManager->get('main.rootMarkerPublic'),
            ],
            true
        );
        $languagesManager = $this->getService(LanguagesManager::class);
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

        $this->tunePlayService = $this->getService(TunePlayService::class);
    }

    public function execute($controller): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $action = $this->getParameter('action') ?? '';

        if ($method === 'GET' && $action === 'tunesByElement') {
            $this->handleTunesByElement();
        } elseif ($method === 'POST' && ($action === '' || $action === 'play')) {
            $this->handlePlay();
        } else {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'Unknown action']);
        }

        $this->renderer->display();
    }

    private function handleTunesByElement(): void
    {
        $elementId = (int)($this->getParameter('elementId') ?? 0);
        if ($elementId <= 0) {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'elementId is required']);
            return;
        }

        try {
            $dtos = $this->getService(TunesService::class)->getByAuthor($elementId);
            $mapper = new ObjectMapper();
            $this->renderer->assign('body', array_map(
                fn(TuneDto $dto) => $mapper->map($dto, TuneRestDto::class),
                $dtos
            ));
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage('Tunes::tunesByElement', $e->getMessage() . "\n" . $e->getTraceAsString());
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }

    private function handlePlay(): void
    {
        $payload = $this->getRequestPayload();
        $tuneId = $payload['tuneId'] ?? $payload['id'] ?? null;
        $tuneId = is_numeric($tuneId) ? (int)$tuneId : 0;

        if ($tuneId === 0) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Missing tuneId');
            return;
        }

        try {
            $this->tunePlayService->logPlay($tuneId);
            $this->renderer->assign('responseStatus', 'success');
        } catch (TuneNotFoundException $exception) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', $exception->getMessage());
        } catch (Throwable) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getRequestPayload(): array
    {
        $raw = file_get_contents('php://input');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $_POST;
    }

    public function getUrlName()
    {
        return '';
    }
}
