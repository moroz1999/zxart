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
use ZxArt\MusicList\MusicListService;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Rest\TuneRestDto;

class Musiclist extends controllerApplication
{
    public $rendererName = 'json';

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        try {
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
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Musiclist::initialize',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            throw $e;
        }
    }

    public function execute($controller): void
    {
        $elementId = (int)($this->getParameter('elementId') ?? 0);
        $compoType = $this->getParameter('compoType') ?: null;

        try {
            if ($elementId <= 0) {
                $this->assignError('elementId is required', 400);
            } else {
                $service = $this->getService(MusicListService::class);
                $dtos = $service->getTunes($elementId, $compoType);
                $mapper = new ObjectMapper();
                $restDtos = array_map(
                    fn(TuneDto $dto) => $mapper->map($dto, TuneRestDto::class),
                    $dtos
                );
                $this->assignSuccess($restDtos);
            }
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Musiclist::execute',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
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

    public function getUrlName(): string
    {
        return '';
    }
}
