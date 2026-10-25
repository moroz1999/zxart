<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Authors\Exception\AuthorAliasFormException;
use ZxArt\Authors\Rest\AuthorAliasFormRestDto;
use ZxArt\Authors\Services\AuthorAliasFormService;

/**
 * The context of the form that adds an alias to an author (`/author-alias-form/`).
 * The alias itself is created through its data endpoint.
 */
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
            $this->getForm();
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

    private function assignError(string $message, int $statusCode): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
