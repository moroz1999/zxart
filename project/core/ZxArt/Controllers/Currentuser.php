<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
use CmsHttpResponse;
use controller;
use controllerApplication;
use ErrorLog;
use Throwable;
use ZxArt\Users\Rest\CurrentUserRestDto;

class Currentuser extends controllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        private readonly CurrentUserService $currentUserService,
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
        $this->handleGet();
        $this->renderer->display();
    }

    protected function handleGet(): void
    {
        try {
            $user = $this->currentUserService->getCurrentUser();
            $userName = $user->userName ?: 'anonymous';
            $id = null;
            if ($userName !== 'anonymous' && $user->id) {
                $id = (int)$user->id;
            }
            $this->renderer->assign('body', new CurrentUserRestDto(id: $id, userName: $userName));
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Currentuser::handleGet',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }
}
