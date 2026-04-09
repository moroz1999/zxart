<?php

declare(strict_types=1);

namespace ZxArt\ProdList;

use structureManager;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\ProdsTransformer;
use ZxArt\Prods\Repositories\ProdsRepository;
use ZxArt\Shared\SortingParams;
use zxProdElement;

readonly class ProdListService
{
    public const array ALLOWED_SORT_COLUMNS = ['title', 'date', 'year', 'votes'];

    public function __construct(
        private structureManager $structureManager,
        private ProdsTransformer $prodsTransformer,
        private ProdsRepository $prodsRepository,
    ) {
    }

    /**
     * Returns a paginated+sorted page of prods linked to an element via a given link type.
     *
     * @return array{total: int, items: ProdDto[]}
     */
    public function getPagedByLinkedElement(
        int $elementId,
        string $linkType,
        SortingParams $sorting,
        int $start,
        int $limit,
    ): array {
        $total = $this->prodsRepository->countByLinkedElement($elementId, $linkType);
        $ids = $this->prodsRepository->findPagedByLinkedElement($elementId, $linkType, $sorting, $start, $limit);

        $items = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id)
                ?? $this->structureManager->getElementById($id, null, true);
            if ($element instanceof zxProdElement) {
                $items[] = $this->prodsTransformer->toDto($element);
            }
        }

        return ['total' => $total, 'items' => $items];
    }
}
