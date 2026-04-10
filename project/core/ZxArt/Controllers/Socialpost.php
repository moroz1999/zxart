<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use Monolog\Logger;
use Override;
use Throwable;
use ZxArt\Social\SocialPostsService;

class Socialpost extends LoggedControllerApplication
{
    public $rendererName = 'smarty';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly SocialPostsService $socialPostsService,
    ) {
        parent::__construct($controller, $logger);
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
        } catch (Throwable $e) {
            $this->logThrowable('Socialpost::execute', $e);
        }
    }
}
