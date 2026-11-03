<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Authors\Services\CreditsReader;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\Elements\ListedFilesReader;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefsReader;
use zxReleaseElement;

/**
 * Builds the assistant's release DTO from the stored element and its
 * production. Checks no privilege.
 *
 * @psalm-api
 */
final readonly class ReleaseDtos
{
    public function __construct(
        private ReleaseRepository $releaseRepository,
        private CreditsReader $creditsReader,
        private ListedFilesReader $listedFilesReader,
        private EntityRefsReader $entityRefsReader,
    ) {
    }

    /**
     * @throws ReleaseDataException for an id of no release
     */
    public function release(int $releaseId): ReleaseDto
    {
        $release = $this->releaseRepository->get($releaseId);
        $publisherIds = ElementFields::ids($release, 'publishers');
        $prodId = ElementFields::ids($release, 'zxProd')[0] ?? 0;
        $prod = $release->getProd();

        return new ReleaseDto(
            id: $releaseId,
            title: ElementFields::text($release, 'title'),
            year: ElementFields::number($release, 'year'),
            version: ElementFields::text($release, 'version'),
            description: ElementFields::text($release, 'description'),
            releaseType: ElementFields::text($release, 'releaseType'),
            releaseFormats: ElementFields::strings($release, 'releaseFormat'),
            languages: ElementFields::strings($release, 'language'),
            hardware: ElementFields::strings($release, 'hardwareRequired'),
            publisherIds: $publisherIds,
            prodId: $prodId,
            refs: $this->entityRefsReader->read([...$publisherIds, $prodId]),
            authors: $this->creditsReader->read($releaseId, EntityType::Release),
            files: $this->listedFilesReader->read($release, ReleaseFileKind::cases()),
            fileName: ElementFields::text($release, 'fileName'),
            fileSize: $this->fileSize($release),
            prodHardware: $prod !== null ? ElementFields::strings($prod, 'hardwareRequired') : [],
            prodLanguages: $prod !== null ? ElementFields::strings($prod, 'language') : [],
        );
    }

    /**
     * Null when the release has no file, or its file is not stored on this
     * server.
     */
    private function fileSize(zxReleaseElement $release): ?int
    {
        if (ElementFields::text($release, 'file') === '') {
            return null;
        }
        $size = is_file($release->getFilePath()) ? filesize($release->getFilePath()) : false;

        return $size !== false ? $size : null;
    }
}
