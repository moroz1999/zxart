<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Authors\Rest\AuthorCoreRestDto;
use ZxArt\Authors\Services\AuthorDetailsService;
use ZxArt\Prods\Exception\ProdDetailsException;

class AuthorDetails extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly AuthorDetailsService $authorDetailsService,
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
            $authorOrAliasId = $this->getAuthorOrAliasId();
            $dto = $this->authorDetailsService->getDetails($authorOrAliasId);
            $this->renderer->assign('body', $this->objectMapper->map($dto, AuthorCoreRestDto::class));
        } catch (ProdDetailsException $e) {
            $this->logThrowable('AuthorDetails::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('AuthorDetails::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getAuthorOrAliasId(): int
    {
        $authorOrAliasId = (int)($this->getParameter('id') ?? 0);
        if ($authorOrAliasId <= 0) {
            throw new ProdDetailsException('Missing required parameter: id', 400);
        }
        return $authorOrAliasId;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
