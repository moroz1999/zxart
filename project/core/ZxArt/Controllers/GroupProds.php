<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Groups\GroupProdsScope;
use ZxArt\Groups\Services\GroupProdsService;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Dto\ProdReleaseDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\Rest\ProdReleaseRestDto;
use ZxArt\Prods\Rest\ProdRestDto;

class GroupProds extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly GroupProdsService $groupProdsService,
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
            $groupId = $this->getGroupId();
            $scope = $this->getScope();
            $start = (int)($this->getParameter('start') ?? 0);
            $limit = (int)($this->getParameter('limit') ?? 50);
            $sort = (string)($this->getParameter('sort') ?? 'year');
            $sortDir = (string)($this->getParameter('sortDir') ?? 'desc');
            $type = (string)($this->getParameter('type') ?? '');
            $categoryId = (int)($this->getParameter('category') ?? 0);

            $result = $this->groupProdsService->getProdsPaged($groupId, $scope, $start, $limit, $sort, $sortDir, $type, $categoryId);
            $this->renderer->assign('body', [
                'items' => array_map(
                    function (array $item): array {
                        if ($item['type'] === 'prod') {
                            /** @var ProdDto $prod */
                            $prod = $item['prod'];
                            $restDto = $this->objectMapper->map($prod, ProdRestDto::class);
                            return [...(array)$restDto, 'type' => 'prod'];
                        }
                        /** @var ProdReleaseDto $release */
                        $release = $item['release'];
                        $restDto = $this->objectMapper->map($release, ProdReleaseRestDto::class);
                        return [...(array)$restDto, 'type' => 'release'];
                    },
                    $result['items'],
                ),
                'total' => $result['total'],
                'availableTypes' => $result['availableTypes'],
                'availableCategories' => $result['availableCategories'],
            ]);
        } catch (ProdDetailsException $e) {
            $this->logThrowable('GroupProds::execute', $e);
            $this->assignError($e->getMessage(), $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logThrowable('GroupProds::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function getGroupId(): int
    {
        $groupId = (int)($this->getParameter('id') ?? 0);
        if ($groupId <= 0) {
            throw new ProdDetailsException('Missing required parameter: id', 400);
        }
        return $groupId;
    }

    private function getScope(): GroupProdsScope
    {
        $scope = GroupProdsScope::tryFrom((string)($this->getParameter('scope') ?? ''));
        if ($scope === null) {
            throw new ProdDetailsException('Invalid or missing parameter: scope', 400);
        }
        return $scope;
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
