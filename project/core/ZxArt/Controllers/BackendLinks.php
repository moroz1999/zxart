<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use controllerApplication;
use ErrorLog;
use Throwable;
use ZxArt\BackendLinks\BackendLinksService;

class BackendLinks extends controllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        private readonly BackendLinksService $backendLinksService,
    ) {
        parent::__construct($controller);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        try {
            $lang = (string)($this->getParameter('lang') ?? '');
            if ($lang === '') {
                CmsHttpResponse::getInstance()->setStatusCode('400');
                $this->renderer->assign('body', ['errorMessage' => 'Missing required parameter: lang']);
                $this->renderer->display();
                return;
            }

            $dto = $this->backendLinksService->getLinks($lang);
            if ($dto === null) {
                CmsHttpResponse::getInstance()->setStatusCode('400');
                $this->renderer->assign('body', ['errorMessage' => 'Unknown language code']);
                $this->renderer->display();
                return;
            }

            $this->renderer->assign('body', $dto);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'BackendLinks::execute',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }
}
