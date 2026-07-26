<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use JsonException;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Authors\Dto\AuthorAliasCreateDto;
use ZxArt\Authors\Exception\AuthorAliasFormException;
use ZxArt\Authors\Rest\AuthorAliasCreatedRestDto;
use ZxArt\Authors\Rest\AuthorAliasFormRestDto;
use ZxArt\Authors\Services\AuthorAliasFormService;

class AuthorAliasForm extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly AuthorAliasFormService $authorAliasFormService,
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
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $this->createAlias($controller);
            } else {
                $this->getForm();
            }
        } catch (AuthorAliasFormException $exception) {
            $this->logThrowable('AuthorAliasForm::execute', $exception);
            $this->assignError($exception->getMessage(), $exception->getStatusCode());
        } catch (Throwable $exception) {
            $this->logThrowable('AuthorAliasForm::execute', $exception);
            $this->assignError('Internal server error', 500);
        }

        $this->renderer->display();
    }

    private function getForm(): void
    {
        $authorId = (int)$this->getParameter('authorId');
        $form = $this->authorAliasFormService->getForm($authorId);
        $this->renderer->assign('body', $this->objectMapper->map($form, AuthorAliasFormRestDto::class));
    }

    private function createAlias(controller $controller): void
    {
        $request = $this->readCreateRequest();
        $created = $this->authorAliasFormService->create($request, $controller);
        CmsHttpResponse::getInstance()->setStatusCode('201');
        $this->renderer->assign('body', $this->objectMapper->map($created, AuthorAliasCreatedRestDto::class));
    }

    private function readCreateRequest(): AuthorAliasCreateDto
    {
        try {
            $body = json_decode((string)file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new AuthorAliasFormException('Invalid request body', 400, $exception);
        }
        if (!is_array($body)) {
            throw new AuthorAliasFormException('Invalid request body', 400);
        }

        return new AuthorAliasCreateDto(
            authorId: (int)($body['authorId'] ?? 0),
            title: (string)($body['title'] ?? ''),
            startDate: (string)($body['startDate'] ?? ''),
            endDate: (string)($body['endDate'] ?? ''),
            displayInMusic: ($body['displayInMusic'] ?? false) === true,
            displayInGraphics: ($body['displayInGraphics'] ?? false) === true,
        );
    }

    private function assignError(string $message, int $statusCode): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
