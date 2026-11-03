<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Authors\Services\CreditsReader;
use ZxArt\LinkTypes;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Releases\Dbal\ReleasesDbal;
use ZxArt\Releases\Services\FoundReleasesReader;
use ZxArt\Shared\Dbal\StructureLinksDbal;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\Elements\ListedFilesReader;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * Builds the assistant's production DTO from the stored element; the series and
 * compilations that include it are read from the links. Checks no privilege.
 *
 * @psalm-api
 */
final readonly class ProdDtos
{
    public function __construct(
        private ProdRepository $prodRepository,
        private CreditsReader $creditsReader,
        private ReleasesDbal $releasesDbal,
        private FoundReleasesReader $foundReleasesReader,
        private ListedFilesReader $listedFilesReader,
        private EntityRefsReader $entityRefsReader,
        private StructureLinksDbal $structureLinksDbal,
    ) {
    }

    /**
     * @throws ProdDataException for an id of no production
     */
    public function prod(int $prodId): ProdDto
    {
        $prod = $this->prodRepository->get($prodId);
        $partyId = ElementFields::number($prod, 'party');
        $categoryIds = array_values(array_map('intval', (array)$prod->getConnectedCategoriesIds()));
        $groupIds = ElementFields::ids($prod, 'groups');
        $publisherIds = ElementFields::ids($prod, 'publishers');
        $compilationItemIds = ElementFields::ids($prod, 'compilationItems');
        $seriesProdIds = ElementFields::ids($prod, 'seriesProds');

        return new ProdDto(
            id: $prodId,
            title: ElementFields::text($prod, 'title'),
            altTitle: ElementFields::text($prod, 'altTitle'),
            year: ElementFields::number($prod, 'year'),
            description: ElementFields::text($prod, 'description'),
            instructions: ElementFields::text($prod, 'instructions'),
            legalStatus: ElementFields::text($prod, 'legalStatus'),
            externalLink: ElementFields::text($prod, 'externalLink'),
            youtubeId: ElementFields::text($prod, 'youtubeId'),
            partyId: $partyId,
            partyPlace: ElementFields::number($prod, 'partyplace'),
            compo: ElementFields::text($prod, 'compo'),
            languages: ElementFields::strings($prod, 'language'),
            hardware: ElementFields::strings($prod, 'hardwareRequired'),
            categoryIds: $categoryIds,
            groupIds: $groupIds,
            publisherIds: $publisherIds,
            tags: $prod->getTagsTexts(),
            compilationItemIds: $compilationItemIds,
            seriesProdIds: $seriesProdIds,
            refs: $this->entityRefsReader->read([$partyId, ...$categoryIds, ...$groupIds, ...$publisherIds, ...$compilationItemIds, ...$seriesProdIds]),
            authors: $this->creditsReader->read($prodId, EntityType::Prod),
            releases: $this->foundReleasesReader->read($this->releasesDbal->findIdsByProdId($prodId)),
            files: $this->listedFilesReader->read($prod, ProdFileKind::cases()),
            inSeries: $this->entityRefsReader->read($this->structureLinksDbal->parentIds($prodId, LinkTypes::SERIES)),
            inCompilations: $this->entityRefsReader->read($this->structureLinksDbal->parentIds($prodId, LinkTypes::COMPILATION)),
        );
    }
}
