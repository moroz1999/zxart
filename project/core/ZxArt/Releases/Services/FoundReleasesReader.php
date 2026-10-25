<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use ZxArt\Authors\Services\CreditsReader;
use ZxArt\Releases\Dto\FoundReleaseDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefs;

/**
 * The summaries of the releases a search found, in the order of the ids.
 *
 * @psalm-api
 */
final readonly class FoundReleasesReader
{
    public function __construct(
        private ReleaseRepository $releaseRepository,
        private CreditsReader $creditsReader,
    ) {
    }

    /**
     * @param int[] $releaseIds
     * @return list<FoundReleaseDto>
     * @throws ReleaseDataException for an id of no release
     */
    public function read(array $releaseIds): array
    {
        $found = [];
        foreach ($releaseIds as $releaseId) {
            $release = $this->releaseRepository->get($releaseId);
            $prod = $release->getProd();
            $found[] = new FoundReleaseDto(
                id: $releaseId,
                title: html_entity_decode(ElementFields::text($release, 'title'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                year: $release->getYear(),
                prod: $prod !== null ? EntityRefs::of($prod) : null,
                releaseType: ElementFields::text($release, 'releaseType'),
                formats: ElementFields::strings($release, 'releaseFormat'),
                fileName: ElementFields::text($release, 'fileName'),
                authors: $this->creditsReader->read($releaseId, EntityType::Release),
                publishers: EntityRefs::list((array)$release->publishers),
            );
        }

        return $found;
    }
}
