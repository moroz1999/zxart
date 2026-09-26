<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Rest\ReleaseDataResultRestDto;
use ZxArt\Releases\Services\ReleaseDataService;

/**
 * Changes to one release: POST `/release-data/?id=&action=` with the action one of
 * `delete`. Answers the id of the element the change produced.
 */
class ReleaseData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ReleaseDataService $releaseDataService,
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
                throw new ReleaseDataException('Method not allowed', 405);
            }
            $releaseId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'delete' => $this->releaseDataService->delete($releaseId),
                default => throw new ReleaseDataException('Unsupported release action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, ReleaseDataResultRestDto::class));
        } catch (ReleaseDataException $exception) {
            $this->logThrowable('ReleaseData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('ReleaseData::execute', $throwable);
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
