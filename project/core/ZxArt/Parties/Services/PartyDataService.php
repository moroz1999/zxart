<?php

declare(strict_types=1);

namespace ZxArt\Parties\Services;

use partyElement;
use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Parties\Dto\PartyDataResultDto;
use ZxArt\Parties\Exception\PartyDataException;
use ZxArt\Shared\StructureType;

/**
 * Changes to a party requested through `/party-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 */
final readonly class PartyDataService
{
    private const string DELETE_PRIVILEGE = 'publicDelete';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
    ) {
    }

    public function delete(int $partyId): PartyDataResultDto
    {
        $party = $this->getParty($partyId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($party, StructureType::Party, self::DELETE_PRIVILEGE);
        $party->deleteElementData();

        return new PartyDataResultDto(id: $partyId);
    }

    private function getParty(int $partyId, string $privilege): partyElement
    {
        if ($partyId <= 0) {
            throw new PartyDataException('Missing required party id', 400);
        }
        $party = $this->structureManager->getElementById($partyId);
        if (!$party instanceof partyElement) {
            throw new PartyDataException('Party not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $partyId,
            $privilege,
            StructureType::Party->value,
        ) === true;
        if (!$isAllowed) {
            throw new PartyDataException('This party change is forbidden', 403);
        }

        return $party;
    }
}
