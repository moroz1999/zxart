<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Rest\AuthorAliasDataResultRestDto;
use ZxArt\Authors\Services\AuthorAliasDataService;

/**
 * Changes to one author alias: POST `/author-alias-data/?id=&action=` with the action one of
 * `delete`, `convertToAuthor`. Answers the id of the element the change produced.
 */
class AuthorAliasData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly AuthorAliasDataService $authorAliasDataService,
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
                throw new AuthorAliasDataException('Method not allowed', 405);
            }
            $aliasId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'delete' => $this->authorAliasDataService->delete($aliasId),
                'convertToAuthor' => $this->authorAliasDataService->convertToAuthor($aliasId),
                default => throw new AuthorAliasDataException('Unsupported author alias action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, AuthorAliasDataResultRestDto::class));
        } catch (AuthorAliasDataException $exception) {
            $this->logThrowable('AuthorAliasData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('AuthorAliasData::execute', $throwable);
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
