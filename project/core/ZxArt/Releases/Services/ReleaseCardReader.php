<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use ZxArt\Authors\Services\CreditsReader;
use ZxArt\Releases\Dto\ReleaseCardDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\Elements\ListedFilesReader;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * @psalm-api
 */
final readonly class ReleaseCardReader
{
    public function __construct(
        private ReleaseRepository $releaseRepository,
        private ReleaseFieldsReader $releaseFieldsReader,
        private CreditsReader $creditsReader,
        private ListedFilesReader $listedFilesReader,
        private EntityRefsReader $entityRefsReader,
    ) {
    }

    /**
     * @throws ReleaseDataException for an id of no release
     */
    public function read(int $releaseId): ReleaseCardDto
    {
        $release = $this->releaseRepository->get($releaseId);
        $fields = $this->releaseFieldsReader->read($release);
        $prod = $release->getProd();
        $fileInfo = $release->getCurrentReleaseFileInfo();

        return new ReleaseCardDto(
            id: $releaseId,
            fields: $fields,
            refs: $this->entityRefsReader->read([...($fields->publisherIds ?? []), $fields->prodId ?? 0]),
            authors: $this->creditsReader->read($releaseId, EntityType::Release),
            files: $this->listedFilesReader->read($release, ReleaseFileKind::cases()),
            fileName: ElementFields::text($release, 'fileName'),
            fileSize: $fileInfo !== null ? $fileInfo['size'] : null,
            prodHardware: $prod !== null ? ElementFields::strings($prod, 'hardwareRequired') : [],
            prodLanguages: $prod !== null ? ElementFields::strings($prod, 'language') : [],
        );
    }
}
