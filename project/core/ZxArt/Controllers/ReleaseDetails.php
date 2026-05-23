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
use ZxArt\Releases\Rest\ReleaseDetailsRestDto;
use ZxArt\Releases\Services\ReleaseDetailsService;

class ReleaseDetails extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ReleaseDetailsService $releaseDetailsService,
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
            $releaseId = $this->getReleaseId();
            $dto = $this->releaseDetailsService->getDetails($releaseId);
            $this->renderer->assign('body', $this->objectMapper->map($dto, ReleaseDetailsRestDto::class));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('ReleaseDetails::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('ReleaseDetails::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getReleaseId(): int
    {
        $releaseId = (int)($this->getParameter('id') ?? 0);
        if ($releaseId <= 0) {
            throw new ProdDetailsException('Missing required parameter: id', 400);
        }

        return $releaseId;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
