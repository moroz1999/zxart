<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Press\Exception\PressDataException;
use ZxArt\Press\Rest\PressDataResultRestDto;
use ZxArt\Press\Services\PressDataService;

/**
 * Changes to one press article: POST `/press-data/?id=&action=` with the action one of
 * `delete`. Answers the id of the element the change produced.
 *
 * @psalm-api
 */
final class PressData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly PressDataService $pressDataService,
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
                throw new PressDataException('Method not allowed', 405);
            }
            $articleId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'delete' => $this->pressDataService->delete($articleId),
                default => throw new PressDataException('Unsupported press article action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, PressDataResultRestDto::class));
        } catch (PressDataException $exception) {
            $this->logThrowable('PressData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('PressData::execute', $throwable);
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
