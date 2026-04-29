<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Tags\Exception\TagsException;
use ZxArt\Tags\Rest\TagsRestDto;
use ZxArt\Tags\TagsService;

class Tags extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly TagsService $tagsService,
        private readonly ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        try {
            $requestMethod = (string)($_SERVER['REQUEST_METHOD'] ?? 'GET');
            if ($requestMethod === 'POST') {
                $this->handleSave();
            } else {
                $this->handleGet();
            }
        } catch (TagsException $e) {
            $this->logThrowable('Tags::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('Tags::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function handleGet(): void
    {
        $elementId = $this->getElementId();
        $tagsDto = $this->tagsService->getTags($elementId);
        $this->renderer->assign('body', $this->objectMapper->map($tagsDto, TagsRestDto::class));
    }

    private function handleSave(): void
    {
        $elementId = $this->getElementId();
        $payload = $this->getRequestPayload();
        $tagsParameter = $payload['tags'] ?? null;
        if (!is_array($tagsParameter)) {
            throw new TagsException('Missing required parameter: tags', 400);
        }

        $tagsDto = $this->tagsService->saveTags($elementId, $tagsParameter);
        $this->renderer->assign('body', $this->objectMapper->map($tagsDto, TagsRestDto::class));
    }

    private function getElementId(): int
    {
        $elementId = (int)($this->getParameter('id') ?? 0);
        if ($elementId <= 0) {
            throw new TagsException('Missing required parameter: id', 400);
        }

        return $elementId;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getRequestPayload(): array
    {
        $raw = file_get_contents('php://input');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $_POST;
    }
}
