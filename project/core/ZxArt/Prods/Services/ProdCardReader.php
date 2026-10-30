<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use ZxArt\Authors\Services\CreditsReader;
use ZxArt\LinkTypes;
use ZxArt\Prods\Dto\ProdCardDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Releases\Dbal\ReleasesDbal;
use ZxArt\Releases\Services\FoundReleasesReader;
use ZxArt\Shared\Dbal\StructureLinksDbal;
use ZxArt\Shared\Elements\ListedFilesReader;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * @psalm-api
 */
final readonly class ProdCardReader
{
    public function __construct(
        private ProdRepository $prodRepository,
        private ProdFieldsReader $prodFieldsReader,
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
    public function read(int $prodId): ProdCardDto
    {
        $prod = $this->prodRepository->get($prodId);
        $fields = $this->prodFieldsReader->read($prod);

        return new ProdCardDto(
            id: $prodId,
            fields: $fields,
            refs: $this->entityRefsReader->read([
                $fields->partyId ?? 0,
                ...($fields->categoryIds ?? []),
                ...($fields->groupIds ?? []),
                ...($fields->publisherIds ?? []),
                ...($fields->compilationItemIds ?? []),
                ...($fields->seriesProdIds ?? []),
            ]),
            authors: $this->creditsReader->read($prodId, EntityType::Prod),
            releases: $this->foundReleasesReader->read($this->releasesDbal->findIdsByProdId($prodId)),
            files: $this->listedFilesReader->read($prod, ProdFileKind::cases()),
            inSeries: $this->entityRefsReader->read($this->structureLinksDbal->parentIds($prodId, LinkTypes::SERIES)),
            inCompilations: $this->entityRefsReader->read($this->structureLinksDbal->parentIds($prodId, LinkTypes::COMPILATION)),
        );
    }
}
