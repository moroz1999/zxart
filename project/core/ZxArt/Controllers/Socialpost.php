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
    protected SocialPostsService $socialPostsService;
    public $rendererName = 'smarty';

    #[Override]
    public function initialize(): void
    {
        $this->createRenderer();
        $this->logger = $this->getService('social_posts_logger');
        $this->socialPostsService = $this->getService(SocialPostsService::class);
    }

    #[Override]
    public function execute($controller): void
    {
        $configManager = $this->getService('ConfigManager');
        $structureManager = $this->getService('structureManager', [
            'rootUrl' => $controller->baseURL,
            'rootMarker' => $configManager->get('main.rootMarkerPublic'),
        ], true);

        try {
            $this->socialPostsService->processQueue();
            $this->logger?->info('Social posts processing completed');
        } catch (\Exception $exception) {
            $this->logger?->error('Social posts processing failed: ' . $exception->getMessage());
        }
    }
}
