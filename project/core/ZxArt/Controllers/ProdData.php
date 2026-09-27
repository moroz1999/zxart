<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Rest\ProdDataResultRestDto;
use ZxArt\Prods\Services\ProdDataService;

/**
 * Changes to one production: POST `/prod-data/?id=&action=` with the action one of
 * `delete`, `deleteMember`, `deleteFile`. Answers the id of the element the change produced.
 *
 * @psalm-api
 */
final class ProdData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ProdDataService $prodDataService,
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
                throw new ProdDataException('Method not allowed', 405);
            }
            $prodId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'delete' => $this->prodDataService->delete($prodId),
                'deleteMember' => $this->prodDataService->deleteMember($prodId, (int)$this->getParameter('authorId')),
                'deleteFile' => $this->prodDataService->deleteFile($prodId, (int)$this->getParameter('fileId')),
                default => throw new ProdDataException('Unsupported production action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, ProdDataResultRestDto::class));
        } catch (ProdDataException $exception) {
            $this->logThrowable('ProdData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('ProdData::execute', $throwable);
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
