<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use ZxArt\Users\CurrentUserRestService;
use ZxArt\Users\Dto\LoginRequestDto;
use ZxArt\Users\LoginService;

class Currentuser extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly CurrentUserRestService $currentUserRestService,
        private readonly LoginService $loginService,
        private readonly SerializerInterface $serializer,
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
            $this->logThrowable('Currentuser::handleGet', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }

    protected function handleLogin(): void
    {
        $body = file_get_contents('php://input');
        if (!is_string($body)) {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'Invalid request body']);
            return;
        }

        try {
            $request = $this->serializer->deserialize($body, LoginRequestDto::class, 'json');
            if ($request->userName === '' || $request->password === '') {
                CmsHttpResponse::getInstance()->setStatusCode('400');
                $this->renderer->assign('body', ['errorMessage' => 'Missing credentials']);
                return;
            }

            $userId = $this->loginService->login($request->userName, $request->password);
            if ($userId === null) {
                CmsHttpResponse::getInstance()->setStatusCode('401');
                $this->renderer->assign('body', ['errorMessage' => 'Invalid credentials']);
                return;
            }

            $this->loginService->switchUser($userId);

            if ($request->remember === true) {
                $this->loginService->remember($request->userName, $userId);
            } else {
                $this->loginService->forget();
            }

            $this->renderer->assign('body', $this->currentUserRestService->buildDto());
        } catch (SerializerException $exception) {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => $exception->getMessage()]);
        } catch (Throwable $e) {
            $this->logThrowable('Currentuser::handleLogin', $e);
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
            $this->logThrowable('Currentuser::handleLogout', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }
}
