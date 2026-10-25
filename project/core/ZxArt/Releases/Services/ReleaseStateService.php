<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use structureManager;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Import\Services\ImportOriginsReader;
use ZxArt\Releases\Dto\ReleaseStateDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Shared\Dto\AttachedFileDto;
use ZxArt\Shared\Elements\AttachedFilesReader;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\EntityType;
use zxReleaseElement;

/**
 * Reads the complete state of a release. Checks no privilege beyond what
 * loading the element checks.
 *
 * @psalm-api
 */
final readonly class ReleaseStateService
{
    public function __construct(
        private structureManager $structureManager,
        private ReleaseFieldsReader $releaseFieldsReader,
        private AuthorshipRepository $authorshipRepository,
        private AttachedFilesReader $attachedFilesReader,
        private ImportOriginsReader $importOriginsReader,
    ) {
    }

    public function get(int $releaseId): ReleaseStateDto
    {
        $release = $this->structureManager->getElementById($releaseId);
        if (!$release instanceof zxReleaseElement) {
            throw new ReleaseDataException('Release not found', 404);
        }

        return new ReleaseStateDto(
            id: $releaseId,
            modifiedAt: $release->getModifiedTimestamp(),
            fields: $this->releaseFieldsReader->read($release),
            authors: $this->authorshipRepository->findByElement($releaseId, EntityType::Release),
            fileName: ElementFields::text($release, 'fileName'),
            fileSize: $this->fileSize($release),
            files: $this->readFiles($release),
            importOrigins: $this->importOriginsReader->read($releaseId),
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

    /**
     * @return array<string, AttachedFileDto[]>
     */
    private function readFiles(zxReleaseElement $release): array
    {
        $files = [];
        foreach (ReleaseFileKind::cases() as $kind) {
            $files[$kind->value] = $this->attachedFilesReader->read($release, $kind);
        }

        return $files;
    }
}
