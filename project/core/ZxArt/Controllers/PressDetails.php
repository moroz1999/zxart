<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Override;
use structureManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Press\Exception\PressDetailsException;
use ZxArt\Press\Rest\PressDetailsRestDto;
use ZxArt\Press\Services\PressDetailsService;

/**
 * JSON detail endpoint for the Angular <zx-press-details> page
 * (`/press-details/?id=`). Mirrors {@see TuneDetails}.
 */
class PressDetails extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly PressDetailsService $pressDetailsService,
        private readonly ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
    }

    #[Override]
    public function execute($controller): void
    {
        try {
            $dto = $this->pressDetailsService->getDetails($this->getArticleId());
            $this->renderer->assign('body', $this->objectMapper->map($dto, PressDetailsRestDto::class));
        } catch (PressDetailsException $e) {
            $this->logThrowable('PressDetails::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PressDetails::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getArticleId(): int
    {
        $articleId = (int)($this->getParameter('id') ?? 0);
        if ($articleId <= 0) {
            throw new PressDetailsException('Missing required parameter: id', 400);
        }

        return $articleId;
    }

    private function assignError(string $message, int $statusCode = 500): void
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
