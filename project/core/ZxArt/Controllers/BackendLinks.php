<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Throwable;
use ZxArt\BackendLinks\BackendLinksService;

class BackendLinks extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly BackendLinksService $backendLinksService,
        private readonly CurrentUserService $currentUserService,
    ) {
        parent::__construct($controller, $logger);
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

            $isAuthenticated = $this->currentUserService->getCurrentUser()->isAuthorized();
            $dto = $this->backendLinksService->getLinks($lang, $isAuthenticated);
            if ($dto === null) {
                CmsHttpResponse::getInstance()->setStatusCode('400');
                $this->renderer->assign('body', ['errorMessage' => 'Unknown language code']);
                $this->renderer->display();
                return;
            }

            $this->renderer->assign('body', $dto);
        } catch (Throwable $e) {
            $this->logThrowable('BackendLinks::execute', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }
}
