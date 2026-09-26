<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
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

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
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

    private function getRelease(int $releaseId, string $privilege): zxReleaseElement
    {
        if ($releaseId <= 0) {
            throw new ReleaseDataException('Missing required release id', 400);
        }
        $release = $this->structureManager->getElementById($releaseId);
        if (!$release instanceof zxReleaseElement) {
            throw new ReleaseDataException('Release not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $releaseId,
            $privilege,
            StructureType::ZxRelease->value,
        ) === true;
        if (!$isAllowed) {
            throw new ReleaseDataException('This release change is forbidden', 403);
        }

        return $release;
    }
}
