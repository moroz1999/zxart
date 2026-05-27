<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\Rest\ProdFilesRestDto;
use ZxArt\Prods\ScreenshotMoveDirection;
use ZxArt\Prods\ScreenshotMoveService;

final class ProdScreenshotMove extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ScreenshotMoveService $screenshotMoveService,
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
            [$elementId, $fileId, $direction] = $this->getValidatedParams();
            $dto = $this->screenshotMoveService->move($elementId, $fileId, $direction);
            $this->renderer->assign('body', $this->objectMapper->map($dto, ProdFilesRestDto::class));
        } catch (ProdDetailsException $exception) {
            $this->logThrowable('ProdScreenshotMove::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $exception) {
            $this->logThrowable('ProdScreenshotMove::execute', $exception);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    /**
     * @return array{int, int, ScreenshotMoveDirection}
     */
    private function getValidatedParams(): array
    {
        $elementId = (int)($this->getParameter('id') ?? 0);
        if ($elementId <= 0) {
            throw new ProdDetailsException('Missing required parameter: id', 400);
        }

        $fileId = (int)($this->getParameter('fileId') ?? 0);
        if ($fileId <= 0) {
            throw new ProdDetailsException('Missing required parameter: fileId', 400);
        }

        $direction = ScreenshotMoveDirection::tryFrom((string)($this->getParameter('direction') ?? ''));
        if ($direction === null) {
            throw new ProdDetailsException('Invalid direction, expected left or right', 400);
        }

        return [$elementId, $fileId, $direction];
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
