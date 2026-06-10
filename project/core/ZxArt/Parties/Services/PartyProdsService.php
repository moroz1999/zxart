<?php

declare(strict_types=1);

namespace ZxArt\Parties\Services;

use partyElement;
use structureManager;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\ProdsTransformer;
use zxProdElement;

/**
 * Provides the party's software entries for a single compo, serialized with the shared
 * {@see ProdsTransformer} (the same model/REST DTO every other prod endpoint uses). The party page
 * loads one compo per request (lazily, per tab) rather than every compo at once.
 */
readonly class PartyProdsService
{
    public function __construct(
        private structureManager $structureManager,
        private ProdsTransformer $prodsTransformer,
    ) {
    }

    /**
     * @return ProdDto[]
     */
    public function getProds(int $partyId, string $compoType): array
    {
        $party = $this->structureManager->getElementById($partyId);
        if (!$party instanceof partyElement) {
            throw new ProdDetailsException('Party not found', 404);
        }

        $compos = $party->getProdsCompos();
        $prods = $compos[$compoType] ?? [];

        $result = [];
        foreach ($prods as $prod) {
            if ($prod instanceof zxProdElement) {
                $result[] = $this->prodsTransformer->toDto($prod);
            }
        }

        return $result;
    }
}
