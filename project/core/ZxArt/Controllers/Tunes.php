<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use ConfigManager;
use controller;
use controllerApplication;
use LanguagesManager;
use Throwable;
use ZxArt\Tunes\Exception\TuneNotFoundException;
use ZxArt\Tunes\Services\TunePlayService;

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
        if ($method !== 'POST') {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Method not allowed');
            $this->renderer->display();
            return;
        }

        $action = $this->getParameter('action');
        if ($action && $action !== 'play') {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Unknown action');
            $this->renderer->display();
            return;
        }

        $this->handlePlay();
        $this->renderer->display();
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
