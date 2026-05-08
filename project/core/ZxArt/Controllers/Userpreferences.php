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
use ZxArt\UserPreferences\DefaultUserPreferencesProvider;
use ZxArt\UserPreferences\Domain\Exception\UserPreferencesException;
use ZxArt\UserPreferences\Rest\PreferenceRestDto;
use ZxArt\UserPreferences\UserPreferencesService;

class Userpreferences extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly ObjectMapper $objectMapper,
        private readonly UserPreferencesService $userPreferencesService,
        private readonly DefaultUserPreferencesProvider $defaultUserPreferencesProvider,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        try {
            $this->startSession('public');
            $this->createRenderer();

            $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
        } catch (Throwable $e) {
            $this->logThrowable('Userpreferences::initialize', $e);
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
            $this->assignError('Method not allowed', 400);
        }

        $this->renderer->display();
    }

    protected function handleGet(): void
    {
        $action = $_GET['action'] ?? null;

        if ($action === 'defaults') {
            $this->handleGetDefaults();
            return;
        }

        try {
            $internalDtos = $this->userPreferencesService->getAllPreferences();
            $restDtos = array_map(
                fn($dto) => $this->objectMapper->map($dto, PreferenceRestDto::class),
                $internalDtos
            );
            $this->assignSuccess($restDtos);
        } catch (Throwable $e) {
            $this->logThrowable('Userpreferences::handleGet', $e);
            $this->assignError('Internal server error');
        }
    }

    protected function handleGetDefaults(): void
    {
        try {
            $defaults = $this->defaultUserPreferencesProvider->getDefaults();

            $restDtos = [];
            foreach ($defaults as $code => $value) {
                $restDtos[] = ['code' => $code, 'value' => $value];
            }

            $this->assignSuccess($restDtos);
        } catch (Throwable $e) {
            $this->logThrowable('Userpreferences::handleGetDefaults', $e);
            $this->assignError('Internal server error');
        }
    }

    protected function handlePut(): void
    {
        $input = file_get_contents('php://input');
        parse_str($input, $params);

        $code = $params['code'] ?? null;
        $value = $params['value'] ?? null;
        $batch = $params['batch'] ?? null;

        try {
            if ($batch !== null) {
                $items = json_decode($batch, true);
                if (!is_array($items)) {
                    $this->assignError('Invalid batch format: expected JSON array', 400);
                    return;
                }
                $values = [];
                foreach ($items as $item) {
                    if (!isset($item['code'], $item['value'])) {
                        $this->assignError('Each batch item must have code and value', 400);
                        return;
                    }
                    $values[(string)$item['code']] = (string)$item['value'];
                }
                $internalDtos = $this->userPreferencesService->setPreferences($values);
            } else {
                if ($code === null || $value === null) {
                    $this->assignError('Missing parameters: code and value required', 400);
                    return;
                }
                $internalDtos = $this->userPreferencesService->setPreference($code, $value);
            }

            $restDtos = array_map(
                fn($dto) => $this->objectMapper->map($dto, PreferenceRestDto::class),
                $internalDtos
            );
            $this->assignSuccess($restDtos);
        } catch (UserPreferencesException $e) {
            $this->logThrowable('Userpreferences::handlePut', $e);
            $this->assignError($e->getMessage(), 400);
        } catch (Throwable $e) {
            $this->logThrowable('Userpreferences::handlePut', $e);
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
}
