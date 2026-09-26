<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Tunes\Exception\TuneDataException;
use ZxArt\Tunes\Rest\TuneDataResultRestDto;
use ZxArt\Tunes\Services\TuneDataService;

/**
 * Changes to one tune: POST `/tune-data/?id=&action=` with the action one of
 * `delete`. Answers the id of the element the change produced.
 */
class TuneData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly TuneDataService $tuneDataService,
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
                throw new TuneDataException('Method not allowed', 405);
            }
            $tuneId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'delete' => $this->tuneDataService->delete($tuneId),
                default => throw new TuneDataException('Unsupported tune action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, TuneDataResultRestDto::class));
        } catch (TuneDataException $exception) {
            $this->logThrowable('TuneData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('TuneData::execute', $throwable);
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
