<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Authors\Services\AuthorProdsService;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\Rest\ProdReleaseRestDto;
use ZxArt\Prods\Rest\ProdRestDto;

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
            $authorOrAliasId = $this->getAuthorOrAliasId();
            $start = (int)($this->getParameter('start') ?? 0);
            $limit = (int)($this->getParameter('limit') ?? 50);
            $sort = (string)($this->getParameter('sort') ?? 'votes');
            $sortDir = (string)($this->getParameter('sortDir') ?? 'desc');
            $role = (string)($this->getParameter('role') ?? '');

            $result = $this->authorProdsService->getProdsPaged($authorOrAliasId, $start, $limit, $sort, $sortDir, $role);
            $this->renderer->assign('body', [
                'items' => array_map(
                    function (array $item): array {
                        if ($item['type'] === 'prod') {
                            $restDto = $this->objectMapper->map($item['prod'], ProdRestDto::class);
                            return [...(array) $restDto, 'type' => 'prod', 'rolesInProd' => $item['rolesInProd']];
                        }
                        $restDto = $this->objectMapper->map($item['release'], ProdReleaseRestDto::class);
                        return [...(array) $restDto, 'type' => 'release', 'rolesInProd' => $item['rolesInProd']];
                    },
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
