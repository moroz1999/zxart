<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Throwable;
use ZxArt\PageMetadata\PageMetadataService;

class PageMetadata extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly PageMetadataService $pageMetadataService,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    #[Override]
    public function execute($controller): void
    {
        try {
            $path = (string)($this->getParameter('path') ?? '/');
            $this->renderer->assign('body', $this->pageMetadataService->getForPath($path));
        } catch (Throwable $throwable) {
            $this->logThrowable('PageMetadata::execute', $throwable);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }
}
