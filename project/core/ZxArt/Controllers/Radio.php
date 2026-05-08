<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use LanguagesManager;
use Monolog\Logger;
use Symfony\Component\ObjectMapper\ObjectMapper;
use structureManager;
use Throwable;
use ZxArt\Radio\Exception\RadioTuneNotFoundException;
use ZxArt\Radio\Services\RadioCriteriaFactory;
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
        private readonly RadioCriteriaFactory $criteriaFactory,
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
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Method not allowed');
            $this->renderer->display();
            return;
        }

        if ($action && $action !== 'next-tune') {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Unknown action');
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
            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $options);
        } catch (Throwable $e) {
            $this->logThrowable('Radio::handleOptions', $e);
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }

    private function handleNextTune(): void
    {
        try {
            $payload = $this->getRequestPayload();
            $criteriaData = $payload['criteria'] ?? null;
            $criteria = is_array($criteriaData)
                ? $this->criteriaFactory->fromArray($criteriaData)
                : $this->criteriaFactory->fromArray([]);

            $tuneDto = $this->radioService->getNextTune($criteria);
            $restDto = $this->objectMapper->map($tuneDto, TuneRestDto::class);

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDto);
        } catch (RadioTuneNotFoundException $exception) {
            $this->logThrowable('Radio::handleNextTune', $exception);
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', $exception->getMessage());
        } catch (Throwable $e) {
            $this->logThrowable('Radio::handleNextTune', $e);
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
