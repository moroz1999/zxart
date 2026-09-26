<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Services;

use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Shared\StructureType;
use ZxArt\Tunes\Dto\TuneDataResultDto;
use ZxArt\Tunes\Exception\TuneDataException;
use zxMusicElement;

/**
 * Changes to a tune requested through `/tune-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class TuneDataService
{
    private const string DELETE_PRIVILEGE = 'publicDelete';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
    ) {
    }

    public function delete(int $tuneId): TuneDataResultDto
    {
        $tune = $this->getTune($tuneId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($tune, StructureType::ZxMusic, self::DELETE_PRIVILEGE);
        $tune->deleteElementData();

        return new TuneDataResultDto(id: $tuneId);
    }

    private function getTune(int $tuneId, string $privilege): zxMusicElement
    {
        if ($tuneId <= 0) {
            throw new TuneDataException('Missing required tune id', 400);
        }
        $tune = $this->structureManager->getElementById($tuneId);
        if (!$tune instanceof zxMusicElement) {
            throw new TuneDataException('Tune not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $tuneId,
            $privilege,
            StructureType::ZxMusic->value,
        ) === true;
        if (!$isAllowed) {
            throw new TuneDataException('This tune change is forbidden', 403);
        }

        return $tune;
    }
}
