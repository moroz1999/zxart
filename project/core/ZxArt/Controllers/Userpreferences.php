<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use controllerApplication;
use ErrorLog;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\UserPreferences\Domain\Exception\UserPreferencesException;
use ZxArt\UserPreferences\Rest\PreferenceRestDto;
use ZxArt\UserPreferences\UserPreferencesService;

class Userpreferences extends controllerApplication
{
    public $rendererName = 'json';
    protected ObjectMapper $objectMapper;
    protected UserPreferencesService $userPreferencesService;

    public function initialize(): void
    {
        try {
            $this->startSession('public');
            $this->createRenderer();
            $this->objectMapper = new ObjectMapper();

            $configManager = $this->getService('ConfigManager');
            $structureManager = $this->getService(
                'structureManager',
                [
                    'rootUrl' => controller::getInstance()->baseURL,
                    'rootMarker' => $configManager->get('main.rootMarkerPublic'),
                ],
                true
            );
            $languagesManager = $this->getService('LanguagesManager');
            $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

            $this->userPreferencesService = $this->getService(UserPreferencesService::class);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage('Userpreferences::initialize', $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }

    public function execute($controller): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($method === 'GET') {
            $this->handleGet();
        } elseif ($method === 'PUT' || $method === 'POST') {
            $this->handlePut();
        } else {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Method not allowed');
        }

        $this->renderer->display();
    }

    protected function handleGet(): void
    {
        try {
            $internalDtos = $this->userPreferencesService->getAllPreferences();
            $restDtos = array_map(
                fn($dto) => $this->objectMapper->map($dto, PreferenceRestDto::class),
                $internalDtos
            );

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDtos);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage('Userpreferences::handleGet', $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }

    protected function handlePut(): void
    {
        $input = file_get_contents('php://input');
        parse_str($input, $params);

        $code = $params['code'] ?? null;
        $value = $params['value'] ?? null;

        if ($code === null || $value === null) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Missing parameters: code and value required');
            return;
        }

        try {
            $internalDtos = $this->userPreferencesService->setPreference($code, $value);
            $restDtos = array_map(
                fn($dto) => $this->objectMapper->map($dto, PreferenceRestDto::class),
                $internalDtos
            );

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDtos);
        } catch (UserPreferencesException $e) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', $e->getMessage());
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage('Userpreferences::handlePut', $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }
}
