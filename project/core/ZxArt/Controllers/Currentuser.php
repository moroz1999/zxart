<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use controllerApplication;
use ErrorLog;
use Throwable;
use ZxArt\Users\CurrentUserRestService;
use ZxArt\Users\LoginService;

class Currentuser extends controllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        private readonly CurrentUserRestService $currentUserRestService,
        private readonly LoginService $loginService,
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
        $action = $this->getParameter('action');

        if ($action === 'login') {
            $this->handleLogin();
        } elseif ($action === 'logout') {
            $this->handleLogout();
        } else {
            $this->handleGet();
        }

        $this->renderer->display();
    }

    protected function handleGet(): void
    {
        try {
            $this->renderer->assign('body', $this->currentUserRestService->buildDto());
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Currentuser::handleGet',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }

    protected function handleLogin(): void
    {
        $body = json_decode((string)file_get_contents('php://input'), true) ?? [];
        $userName = isset($body['userName']) ? (string)$body['userName'] : '';
        $password = isset($body['password']) ? (string)$body['password'] : '';
        $remember = isset($body['remember']) && $body['remember'] === true;

        if ($userName === '' || $password === '') {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'Missing credentials']);
            return;
        }

        try {
            $userId = $this->loginService->login($userName, $password);
            if ($userId === null) {
                CmsHttpResponse::getInstance()->setStatusCode('401');
                $this->renderer->assign('body', ['errorMessage' => 'Invalid credentials']);
                return;
            }

            $this->loginService->switchUser($userId);

            if ($remember) {
                $this->loginService->remember($userName, $userId);
            } else {
                $this->loginService->forget();
            }

            $this->renderer->assign('body', $this->currentUserRestService->buildDto());
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Currentuser::handleLogin',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }

    protected function handleLogout(): void
    {
        try {
            $this->loginService->logout();
            $this->renderer->assign('body', $this->currentUserRestService->buildAnonymousDto());
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Currentuser::handleLogout',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }
}
