<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use fileElement;
use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use zxReleaseElement;

/**
 * Changes to a release requested through `/release-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class ReleaseDataService
{
    private const string DELETE_PRIVILEGE = 'publicDelete';
    private const string DELETE_MEMBER_PRIVILEGE = 'deleteAuthor';
    private const string DELETE_FILE_PRIVILEGE = 'delete';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
        private AuthorshipRepository $authorshipRepository,
    ) {
    }

    public function delete(int $releaseId): ReleaseDataResultDto
    {
        $release = $this->getRelease($releaseId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($release, StructureType::ZxRelease, self::DELETE_PRIVILEGE);
        $release->deleteElementData();

        return new ReleaseDataResultDto(id: $releaseId);
    }

    /**
     * Removes the author from the release's members.
     */
    public function deleteMember(int $releaseId, int $authorId): ReleaseDataResultDto
    {
        $release = $this->getRelease($releaseId, self::DELETE_MEMBER_PRIVILEGE);
        if ($authorId <= 0) {
            throw new ReleaseDataException('Missing required author id', 400);
        }
        $isDeleted = $this->authorshipRepository->deleteAuthorship($releaseId, $authorId, EntityType::Release);
        if (!$isDeleted) {
            throw new ReleaseDataException('The author is not a member of this release', 404);
        }
        $this->actionsLogService->log($release, StructureType::ZxRelease, self::DELETE_MEMBER_PRIVILEGE);

        return new ReleaseDataResultDto(id: $releaseId);
    }

    /**
     * Deletes one file of the release's multi-file selectors (screenshots,
     * inlays and the like). The file carries the privilege itself.
     */
    public function deleteFile(int $releaseId, int $fileId): ReleaseDataResultDto
    {
        $file = $this->getSelectorFile($this->findRelease($releaseId), $fileId);
        $this->assertAllowed($fileId, self::DELETE_FILE_PRIVILEGE, StructureType::File);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($file, StructureType::File, self::DELETE_FILE_PRIVILEGE);
        $file->deleteElementData();

        return new ReleaseDataResultDto(id: $releaseId);
    }

    private function getRelease(int $releaseId, string $privilege): zxReleaseElement
    {
        $release = $this->findRelease($releaseId);
        $this->assertAllowed($releaseId, $privilege, StructureType::ZxRelease);

        return $release;
    }

    private function findRelease(int $releaseId): zxReleaseElement
    {
        if ($releaseId <= 0) {
            throw new ReleaseDataException('Missing required release id', 400);
        }
        $release = $this->structureManager->getElementById($releaseId);
        if (!$release instanceof zxReleaseElement) {
            throw new ReleaseDataException('Release not found', 404);
        }

        return $release;
    }

    private function assertAllowed(int $elementId, string $privilege, StructureType $structureType): void
    {
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            $privilege,
            $structureType->value,
        ) === true;
        if (!$isAllowed) {
            throw new ReleaseDataException('This release change is forbidden', 403);
        }
    }

    private function getSelectorFile(zxReleaseElement $release, int $fileId): fileElement
    {
        if ($fileId <= 0) {
            throw new ReleaseDataException('Missing required file id', 400);
        }
        foreach ($release->getFileSelectorPropertyNames() as $propertyName) {
            foreach ($release->getFilesList($propertyName) as $file) {
                if ($file->getId() === $fileId) {
                    return $file;
                }
            }
        }

        throw new ReleaseDataException('The file does not belong to this release', 404);
    }
}
