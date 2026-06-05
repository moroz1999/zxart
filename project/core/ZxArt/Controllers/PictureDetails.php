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
use ZxArt\Pictures\Exception\PictureDetailsException;
use ZxArt\Pictures\Rest\PictureDetailsRestDto;
use ZxArt\Pictures\Services\PictureDetailsService;

class PictureDetails extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly PictureDetailsService $pictureDetailsService,
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
            $pictureId = $this->getPictureId();
            $dto = $this->pictureDetailsService->getDetails($pictureId);
            $this->renderer->assign('body', $this->objectMapper->map($dto, PictureDetailsRestDto::class));
        } catch (PictureDetailsException $e) {
            $this->logThrowable('PictureDetails::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('PictureDetails::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getPictureId(): int
    {
        $pictureId = (int)($this->getParameter('id') ?? 0);
        if ($pictureId <= 0) {
            throw new PictureDetailsException('Missing required parameter: id', 400);
        }

        return $pictureId;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    public function getUrlName(): string
    {
        return '';
    }
}
