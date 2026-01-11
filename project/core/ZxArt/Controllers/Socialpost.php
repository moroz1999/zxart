<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use controllerApplication;
use Monolog\Logger;
use Override;
use ZxArt\Social\SocialPostsService;

class Socialpost extends controllerApplication
{
    private ?Logger $logger = null;
    public $rendererName = 'smarty';

    #[Override]
    public function initialize(): void
    {
        $this->createRenderer();
        $this->logger = $this->getService('social_posts_logger');
    }

    #[Override]
    public function execute($controller): void
    {
        $configManager = $this->getService('ConfigManager');
        $structureManager = $this->getService('structureManager', [
            'rootUrl' => $controller->baseURL,
            'rootMarker' => $configManager->get('main.rootMarkerPublic'),
        ], true);

        $socialPostsService = $this->getService(SocialPostsService::class);

        try {
            $socialPostsService->processQueue();
            $this->logger?->info('Social posts processing completed');
        } catch (\Exception $exception) {
            $this->logger?->error('Social posts processing failed: ' . $exception->getMessage());
        }
    }
}
