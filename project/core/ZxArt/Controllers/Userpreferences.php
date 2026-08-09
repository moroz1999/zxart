<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use structureManager;
use Throwable;
use ZxArt\UserPreferences\DefaultUserPreferencesProvider;
use ZxArt\UserPreferences\Domain\Exception\UserPreferencesException;
use ZxArt\UserPreferences\Dto\PreferencesUpdateRequestDto;
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
        private readonly SerializerInterface $serializer,
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
        try {
            $request = $this->serializer->deserialize(
                file_get_contents('php://input'),
                PreferencesUpdateRequestDto::class,
                'json',
            );
            $internalDtos = $this->userPreferencesService->setPreferences($request->preferences);

            $restDtos = array_map(
                fn($dto) => $this->objectMapper->map($dto, PreferenceRestDto::class),
                $internalDtos
            );
            $this->assignSuccess($restDtos);
        } catch (SerializerException $exception) {
            $this->assignError($exception->getMessage(), 400);
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
