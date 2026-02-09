<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use controllerApplication;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Radio\Domain\RadioPreset;
use ZxArt\Radio\Exception\RadioTuneNotFoundException;
use ZxArt\Radio\Services\RadioCriteriaFactory;
use ZxArt\Radio\Services\RadioOptionsService;
use ZxArt\Radio\Services\RadioService;
use ZxArt\Tunes\Rest\TuneRestDto;

class Radio extends controllerApplication
{
    public $rendererName = 'json';
    protected ObjectMapper $objectMapper;
    protected RadioService $radioService;
    protected RadioOptionsService $optionsService;
    protected RadioCriteriaFactory $criteriaFactory;

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
        $this->objectMapper = new ObjectMapper();

        $configManager = $this->getService('ConfigManager');
        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => controller::getInstance()->rootURL,
                'rootMarker' => $configManager->get('main.rootMarkerPublic'),
            ],
            true
        );
        $languagesManager = $this->getService('LanguagesManager');
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

        $this->radioService = $this->getService(RadioService::class);
        $this->optionsService = $this->getService(RadioOptionsService::class);
        $this->criteriaFactory = $this->getService(RadioCriteriaFactory::class);
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
        } catch (Throwable) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }

    private function handleNextTune(): void
    {
        try {
            $payload = $this->getRequestPayload();
            $criteriaData = $payload['criteria'] ?? null;
            $presetValue = $payload['preset'] ?? null;

            if (is_array($criteriaData)) {
                $criteria = $this->criteriaFactory->fromArray($criteriaData);
            } elseif (is_string($presetValue) && ($preset = RadioPreset::tryFrom($presetValue))) {
                $criteria = $this->criteriaFactory->fromPreset($preset);
            } else {
                $criteria = $this->criteriaFactory->fromArray([]);
            }

            $tuneDto = $this->radioService->getNextTune($criteria);
            $restDto = $this->objectMapper->map($tuneDto, TuneRestDto::class);

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDto);
        } catch (RadioTuneNotFoundException $exception) {
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
