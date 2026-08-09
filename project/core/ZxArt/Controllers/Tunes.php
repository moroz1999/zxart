<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use structureManager;
use Throwable;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Dto\TunePlayRequestDto;
use ZxArt\Tunes\Exception\TuneNotFoundException;
use ZxArt\Tunes\Rest\TuneRestDto;
use ZxArt\Tunes\Services\TunePlayService;
use ZxArt\Tunes\Services\TunesService;

class Tunes extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly TunePlayService $tunePlayService,
        private readonly TunesService $tunesService,
        private readonly ObjectMapper $objectMapper,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
    }

    public function execute($controller): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $action = $this->getParameter('action') ?? '';

        if ($method === 'GET' && $action === 'tunesByElement') {
            $this->handleTunesByElement();
        } elseif ($method === 'POST' && ($action === '' || $action === 'play')) {
            $this->handlePlay();
        } else {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'Unknown action']);
        }

        $this->renderer->display();
    }

    private function handleTunesByElement(): void
    {
        $elementId = (int)($this->getParameter('elementId') ?? 0);
        if ($elementId <= 0) {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => 'elementId is required']);
            return;
        }

        $limit = (int)($this->getParameter('limit') ?? 0);

        try {
            if ($limit > 0) {
                $start = (int)($this->getParameter('start') ?? 0);
                $sortColumn = (string)($this->getParameter('sortColumn') ?? 'votes');
                $sortDir = (string)($this->getParameter('sortDir') ?? 'desc');
                $formatGroupFilter = (string)($this->getParameter('formatGroup') ?? '');
                $result = $this->tunesService->getByAuthorPaged($elementId, $start, $limit, $sortColumn, $sortDir, $formatGroupFilter);
                $this->renderer->assign('body', [
                    'items' => array_map(fn(TuneDto $dto) => $this->objectMapper->map($dto, TuneRestDto::class), $result['items']),
                    'total' => $result['total'],
                    'availableFormatGroups' => $result['availableFormatGroups'],
                ]);
            } else {
                $dtos = $this->tunesService->getByAuthor($elementId);
                $this->renderer->assign('body', array_map(
                    fn(TuneDto $dto) => $this->objectMapper->map($dto, TuneRestDto::class),
                    $dtos
                ));
            }
        } catch (Throwable $e) {
            $this->logThrowable('Tunes::tunesByElement', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }

    private function handlePlay(): void
    {
        try {
            $request = $this->serializer->deserialize(
                file_get_contents('php://input'),
                TunePlayRequestDto::class,
                'json',
            );
            $this->tunePlayService->logPlay($request->tuneId);
            $this->renderer->assign('body', ['success' => true]);
        } catch (SerializerException $exception) {
            CmsHttpResponse::getInstance()->setStatusCode('400');
            $this->renderer->assign('body', ['errorMessage' => $exception->getMessage()]);
        } catch (TuneNotFoundException $exception) {
            $this->logThrowable('Tunes::handlePlay', $exception);
            CmsHttpResponse::getInstance()->setStatusCode('404');
            $this->renderer->assign('body', ['errorMessage' => $exception->getMessage()]);
        } catch (Throwable $e) {
            $this->logThrowable('Tunes::handlePlay', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }
    }

    public function getUrlName()
    {
        return '';
    }
}
