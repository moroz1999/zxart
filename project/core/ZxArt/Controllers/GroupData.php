<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Rest\GroupDataResultRestDto;
use ZxArt\Groups\Services\GroupDataService;

/**
 * Changes to one group: POST `/group-data/?id=&action=` with the action one of
 * `delete`, `convertToAuthor`. Answers the id of the element the change produced.
 */
class GroupData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly GroupDataService $groupDataService,
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
                throw new GroupDataException('Method not allowed', 405);
            }
            $groupId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'delete' => $this->groupDataService->delete($groupId),
                'convertToAuthor' => $this->groupDataService->convertToAuthor($groupId),
                default => throw new GroupDataException('Unsupported group action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, GroupDataResultRestDto::class));
        } catch (GroupDataException $exception) {
            $this->logThrowable('GroupData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('GroupData::execute', $throwable);
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
