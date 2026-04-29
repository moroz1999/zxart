<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Throwable;
use ZxArt\ElementPrivileges\ElementPrivilegesService;
use ZxArt\Tags\Exception\TagsException;

class ElementPrivileges extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ElementPrivilegesService $elementPrivilegesService,
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
            $elementId = (int)($this->getParameter('id') ?? 0);
            if ($elementId <= 0) {
                throw new TagsException('Missing required parameter: id', 400);
            }

            $privilegesParameter = (string)($this->getParameter('privileges') ?? '');
            if ($privilegesParameter === '') {
                throw new TagsException('Missing required parameter: privileges', 400);
            }

            $requestedPrivileges = explode(',', $privilegesParameter);
            $dto = $this->elementPrivilegesService->getPrivileges($elementId, $requestedPrivileges);
            $this->renderer->assign('body', $dto->privileges);
        } catch (TagsException $e) {
            $this->logThrowable('ElementPrivileges::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('ElementPrivileges::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
