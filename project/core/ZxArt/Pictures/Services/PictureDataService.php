<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Services;

use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Pictures\Dto\PictureDataResultDto;
use ZxArt\Pictures\Exception\PictureDataException;
use ZxArt\Shared\StructureType;
use zxPictureElement;

/**
 * Changes to a picture requested through `/picture-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 */
final readonly class PictureDataService
{
    private const string DELETE_PRIVILEGE = 'publicDelete';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
    ) {
    }

    public function delete(int $pictureId): PictureDataResultDto
    {
        $picture = $this->getPicture($pictureId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($picture, StructureType::ZxPicture, self::DELETE_PRIVILEGE);
        $picture->deleteElementData();

        return new PictureDataResultDto(id: $pictureId);
    }

    private function getPicture(int $pictureId, string $privilege): zxPictureElement
    {
        if ($pictureId <= 0) {
            throw new PictureDataException('Missing required picture id', 400);
        }
        $picture = $this->structureManager->getElementById($pictureId);
        if (!$picture instanceof zxPictureElement) {
            throw new PictureDataException('Picture not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $pictureId,
            $privilege,
            StructureType::ZxPicture->value,
        ) === true;
        if (!$isAllowed) {
            throw new PictureDataException('This picture change is forbidden', 403);
        }

        return $picture;
    }
}
