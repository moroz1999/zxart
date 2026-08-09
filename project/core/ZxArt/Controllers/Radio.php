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
use ZxArt\Radio\Dto\RadioCriteriaDto;
use ZxArt\Radio\Dto\RadioNextTuneRequestDto;
use ZxArt\Radio\Exception\RadioTuneNotFoundException;
use ZxArt\Radio\Services\RadioOptionsService;
use ZxArt\Radio\Services\RadioService;
use ZxArt\Tunes\Rest\TuneRestDto;

class Radio extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly ObjectMapper $objectMapper,
        private readonly RadioService $radioService,
        private readonly RadioOptionsService $optionsService,
        private readonly SerializerInterface $serializer,
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
        $action = $this->getParameter('action');

        if ($method === 'GET' && $action === 'options') {
            $this->handleOptions();
            $this->renderer->display();
            return;
        }

        if ($method !== 'POST') {
            $this->assignError('Method not allowed', 405);
            $this->renderer->display();
            return;
        }

        if ($action !== false && $action !== '' && $action !== 'next-tune') {
            $this->assignError('Unknown action', 400);
            $this->renderer->display();
            return;
        }

        $this->handleNextTune();
        $this->renderer->display();
    }

    private function handleOptions(): void
    {
        try {
            $options = $this->optionsService->getOptions();
            $this->renderer->assign('body', $options);
        } catch (Throwable $e) {
            $this->logThrowable('Radio::handleOptions', $e);
            $this->assignError('Internal server error', 500);
        }
    }

    private function handleNextTune(): void
    {
        try {
            $body = file_get_contents('php://input');
            $request = $body === ''
                ? new RadioNextTuneRequestDto()
                : $this->serializer->deserialize($body, RadioNextTuneRequestDto::class, 'json');
            $criteria = $request->criteria ?? new RadioCriteriaDto();

            $tuneDto = $this->radioService->getNextTune($criteria);
            $restDto = $this->objectMapper->map($tuneDto, TuneRestDto::class);

            $this->renderer->assign('body', $restDto);
        } catch (SerializerException $exception) {
            $this->assignError($exception->getMessage(), 400);
        } catch (RadioTuneNotFoundException $exception) {
            $this->logThrowable('Radio::handleNextTune', $exception);
            $this->assignError($exception->getMessage(), 404);
        } catch (Throwable $e) {
            $this->logThrowable('Radio::handleNextTune', $e);
            $this->assignError('Internal server error', 500);
        }
    }

    private function assignError(string $message, int $statusCode): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    public function getUrlName()
    {
        return '';
    }
}
