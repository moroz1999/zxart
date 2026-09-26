<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Pictures\Exception\PictureDataException;
use ZxArt\Pictures\Rest\PictureDataResultRestDto;
use ZxArt\Pictures\Services\PictureDataService;

/**
 * Changes to one picture: POST `/picture-data/?id=&action=` with the action one of
 * `delete`. Answers the id of the element the change produced.
 */
class PictureData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly PictureDataService $pictureDataService,
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
                throw new PictureDataException('Method not allowed', 405);
            }
            $pictureId = (int)$this->getParameter('id');
            $result = match ((string)$this->getParameter('action')) {
                'delete' => $this->pictureDataService->delete($pictureId),
                default => throw new PictureDataException('Unsupported picture action', 400),
            };
            $this->renderer->assign('body', $this->objectMapper->map($result, PictureDataResultRestDto::class));
        } catch (PictureDataException $exception) {
            $this->logThrowable('PictureData::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $throwable) {
            $this->logThrowable('PictureData::execute', $throwable);
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
