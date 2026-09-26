<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Rest\GroupAliasDataResultRestDto;
use ZxArt\Groups\Services\GroupAliasDataService;

/**
 * Changes to one group alias: POST `/group-alias-data/?id=&action=` with the action one of
 * `delete`, `convertToGroup`. Answers the id of the element the change produced.
 */
class GroupAliasData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly GroupAliasDataService $groupAliasDataService,
        private readonly ObjectMapper $objectMapper,
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
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                throw new GroupAliasDataException('Method not allowed', 405);
            }
            $aliasId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'delete' => $this->groupAliasDataService->delete($aliasId),
                'convertToGroup' => $this->groupAliasDataService->convertToGroup($aliasId),
                default => throw new GroupAliasDataException('Unsupported group alias action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, GroupAliasDataResultRestDto::class));
        } catch (GroupAliasDataException $exception) {
            $this->logThrowable('GroupAliasData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('GroupAliasData::execute', $throwable);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    private function assignError(string $message, int $statusCode): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
