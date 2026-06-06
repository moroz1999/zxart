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
use ZxArt\Tunes\Exception\TuneDetailsException;
use ZxArt\Tunes\Rest\TuneDetailsRestDto;
use ZxArt\Tunes\Services\TuneDetailsService;

class TuneDetails extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly TuneDetailsService $tuneDetailsService,
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
            $tuneId = $this->getTuneId();
            $dto = $this->tuneDetailsService->getDetails($tuneId);
            $this->renderer->assign('body', $this->objectMapper->map($dto, TuneDetailsRestDto::class));
        } catch (TuneDetailsException $e) {
            $this->logThrowable('TuneDetails::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('TuneDetails::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getTuneId(): int
    {
        $tuneId = (int)($this->getParameter('id') ?? 0);
        if ($tuneId <= 0) {
            throw new TuneDetailsException('Missing required parameter: id', 400);
        }

        return $tuneId;
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
