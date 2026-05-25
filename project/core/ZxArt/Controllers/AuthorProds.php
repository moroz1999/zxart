<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Authors\Dto\AuthorProdDto;
use ZxArt\Authors\Rest\AuthorProdRestDto;
use ZxArt\Authors\Services\AuthorProdsService;
use ZxArt\Prods\Exception\ProdDetailsException;

class AuthorProds extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly AuthorProdsService $authorProdsService,
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
            $authorId = $this->getAuthorId();
            $start = (int)($this->getParameter('start') ?? 0);
            $limit = (int)($this->getParameter('limit') ?? 50);
            $sort = (string)($this->getParameter('sort') ?? 'votes');
            $sortDir = (string)($this->getParameter('sortDir') ?? 'desc');
            $role = (string)($this->getParameter('role') ?? '');

            $result = $this->authorProdsService->getProdsPaged($authorId, $start, $limit, $sort, $sortDir, $role);
            $this->renderer->assign('body', [
                'items' => array_map(
                    fn(AuthorProdDto $dto) => $this->objectMapper->map($dto, AuthorProdRestDto::class),
                    $result['items'],
                ),
                'total' => $result['total'],
                'availableRoles' => $result['availableRoles'],
            ]);
        } catch (ProdDetailsException $e) {
            $this->logThrowable('AuthorProds::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('AuthorProds::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getAuthorId(): int
    {
        $authorId = (int)($this->getParameter('id') ?? 0);
        if ($authorId <= 0) {
            throw new ProdDetailsException('Missing required parameter: id', 400);
        }
        return $authorId;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
