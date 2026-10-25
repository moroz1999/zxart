<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use ZxArt\Authors\Services\CreditsReader;
use ZxArt\Prods\Dto\FoundProdDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefs;

/**
 * The summaries of the productions a search found, in the order of the ids.
 *
 * @psalm-api
 */
final readonly class FoundProdsReader
{
    public function __construct(
        private ProdRepository $prodRepository,
        private CreditsReader $creditsReader,
    ) {
    }

    /**
     * @param int[] $prodIds
     * @return list<FoundProdDto>
     * @throws ProdDataException for an id of no production
     */
    public function read(array $prodIds): array
    {
        $found = [];
        foreach ($prodIds as $prodId) {
            $prod = $this->prodRepository->get($prodId);
            $year = ElementFields::number($prod, 'year');
            $found[] = new FoundProdDto(
                id: $prodId,
                title: html_entity_decode(ElementFields::text($prod, 'title'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                year: $year > 0 ? $year : null,
                authors: $this->creditsReader->read($prodId, EntityType::Prod),
                groups: EntityRefs::list((array)$prod->groups),
                publishers: EntityRefs::list((array)$prod->publishers),
                categories: EntityRefs::list($prod->getConnectedCategories()),
            );
        }

        return $found;
    }
}
