<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use authorElement;
use structureManager;
use zxProdElement;
use zxReleaseElement;
use ZxArt\Authors\Repositories\AuthorProdsRepository;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\ProdsTransformer;
use ZxArt\Releases\ReleasesTransformer;

readonly class AuthorProdsService
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorProdsRepository $authorProdsRepository,
        private ProdsTransformer $prodsTransformer,
        private ReleasesTransformer $releasesTransformer,
    ) {
    }

    /**
     * @return array{items: list<array{prod: ProdDto, rolesInProd: string[]}>, total: int, availableRoles: string[]}
     */
    public function getProdsPaged(
        int $authorId,
        int $start,
        int $limit,
        string $sort,
        string $sortDir,
        string $role,
    ): array {
        $author = $this->structureManager->getElementById($authorId);
        if (!($author instanceof authorElement) && !($author instanceof authorAliasElement)) {
            throw new ProdDetailsException('Author or alias not found', 404);
        }

        $page = $this->authorProdsRepository->findPagedByAuthorId($authorId, $start, $limit, $sort, $sortDir, $role);
        $availableRoles = $this->authorProdsRepository->findAvailableRolesByAuthorId($authorId);

        $items = [];
        foreach ($page['items'] as $item) {
            $element = $this->structureManager->getElementById($item['id']);
            $prod = match (true) {
                $element instanceof zxProdElement => $this->prodsTransformer->toDto($element),
                $element instanceof zxReleaseElement => $this->releasesTransformer->toProdDto($element),
                default => null,
            };
            if ($prod !== null) {
                $items[] = ['prod' => $prod, 'rolesInProd' => $item['roles']];
            }
        }

        return ['items' => $items, 'total' => $page['total'], 'availableRoles' => $availableRoles];
    }
}
