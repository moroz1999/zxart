<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use ZxArt\Tags\Dto\TagsSaveRequestDto;
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
        private readonly SerializerInterface $serializer,
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
            $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            if ($requestMethod === 'POST') {
                $this->handleSave();
            } else {
                $this->handleGet();
            }
        } catch (TagsException $e) {
            $this->logThrowable('Tags::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (SerializerException $exception) {
            $this->assignError($exception->getMessage(), 400);
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
        $request = $this->serializer->deserialize(
            file_get_contents('php://input'),
            TagsSaveRequestDto::class,
            'json',
        );

        $tagsDto = $this->tagsService->saveTags($elementId, $request->tags);
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

}
