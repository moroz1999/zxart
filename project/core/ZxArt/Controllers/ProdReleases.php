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
use ZxArt\Prods\ProdReleasesService;
use ZxArt\Prods\Rest\ProdReleasesRestDto;

class ProdReleases extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly ProdReleasesService $prodReleasesService,
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
            $elementId = $this->getElementId();
            $releasesDto = $this->prodReleasesService->getReleases($elementId);
            $this->renderer->assign('body', $this->objectMapper->map($releasesDto, ProdReleasesRestDto::class));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('ProdReleases::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('ProdReleases::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getElementId(): int
    {
        $elementId = (int)($this->getParameter('id') ?? 0);
        if ($elementId <= 0) {
            throw new ProdDetailsException('Missing required parameter: id', 400);
        }

        return $elementId;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
