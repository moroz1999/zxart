<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use controllerApplication;
use Monolog\Logger;
use Override;
use ZxArt\Social\SocialPostsService;

class Socialpost extends controllerApplication
{
    public $rendererName = 'smarty';

    public function __construct(
        controller $controller,
        string $applicationName,
        private readonly SocialPostsService $socialPostsService,
        private readonly Logger $logger,
    ) {
        parent::__construct($controller, $applicationName);
    }

    #[Override]
    public function initialize(): void
    {
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller): void
    {
        try {
            $this->socialPostsService->processQueue();
            $this->logger->info('Social posts processing completed');
        } catch (\Exception $exception) {
            $this->logger->error('Social posts processing failed: ' . $exception->getMessage());
        }
    }
}
