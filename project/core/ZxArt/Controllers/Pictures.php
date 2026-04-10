<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use ConfigManager;
use controller;
use LanguagesManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Pictures\Services\PicturesService;

class Pictures extends LoggedControllerApplication
{
    public $rendererName = 'json';

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
    }

    public function execute($controller): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $action = $this->getParameter('action') ?? '';

        if ($method === 'GET' && $action === 'picturesByElement') {
            $this->handlePicturesByElement();
        } else {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'Unknown action']);
        }

        $this->renderer->display();
    }

    private function handlePicturesByElement(): void
    {
        $elementId = (int)($this->getParameter('elementId') ?? 0);
        if ($elementId <= 0) {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'elementId is required']);
            return;
        }

        try {
            $dtos = $this->getService(PicturesService::class)->getByAuthor($elementId);
            $mapper = new ObjectMapper();
            $this->renderer->assign('body', array_map(
                fn(PictureDto $dto) => $mapper->map($dto, PictureRestDto::class),
                $dtos
            ));
        } catch (Throwable $e) {
            $this->logThrowable('Pictures::picturesByElement', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }

    public function getUrlName()
    {
        return '';
    }
}
