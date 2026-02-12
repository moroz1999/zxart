<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
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
        string $applicationName,
        private readonly CurrentUserService $currentUserService,
    ) {
        parent::__construct($controller, $applicationName);
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
            $restDto = new CurrentUserRestDto(id: $id, userName: $userName);

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDto);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Currentuser::handleGet',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }
}
