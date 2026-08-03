<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Prods\Rest\ProdFilesRestDto;
use ZxArt\Screenshots\Exception\ScreenshotUploadException;
use ZxArt\Screenshots\ScreenshotFormat;
use ZxArt\Screenshots\ScreenshotUploadService;

/**
 * Screenshot upload entry point: the raw screen dump is the request body, the
 * target prod or release is addressed by `id`, its dump format by `format`.
 */
final class ScreenshotUpload extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ScreenshotUploadService $screenshotUploadService,
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
            $this->assertKnownFormat();
            $dto = $this->screenshotUploadService->upload($this->getValidatedElementId());
            $this->renderer->assign('body', $this->objectMapper->map($dto, ProdFilesRestDto::class));
        } catch (ScreenshotUploadException $exception) {
            $this->logThrowable('ScreenshotUpload::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $exception) {
            $this->logThrowable('ScreenshotUpload::execute', $exception);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getValidatedElementId(): int
    {
        $elementId = (int)($this->getParameter('id') ?? 0);
        if ($elementId <= 0) {
            throw new ScreenshotUploadException('Missing required parameter: id', 400);
        }

        return $elementId;
    }

    /**
     * The format itself is consumed by the element's `uploadScreenshot` action;
     * rejecting an unknown one here keeps the endpoint from answering 200 to a
     * request that could never store anything.
     */
    private function assertKnownFormat(): void
    {
        $format = ScreenshotFormat::tryFrom((string)($this->getParameter('format') ?? ''));
        if ($format === null) {
            throw new ScreenshotUploadException('Missing or unknown parameter: format', 400);
        }
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
