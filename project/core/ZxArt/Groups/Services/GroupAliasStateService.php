<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupAliasElement;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Groups\Dto\GroupAliasStateDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Import\Services\ImportOriginsReader;
use ZxArt\Shared\EntityType;

/**
 * Reads the complete state of a group alias. Checks no privilege beyond what
 * loading the element checks.
 *
 * @psalm-api
 */
final readonly class GroupAliasStateService
{
    public function __construct(
        private structureManager $structureManager,
        private GroupAliasFieldsReader $groupAliasFieldsReader,
        private AuthorshipRepository $authorshipRepository,
        private GroupDetailsService $groupDetailsService,
        private ImportOriginsReader $importOriginsReader,
    ) {
    }

    public function get(int $aliasId): GroupAliasStateDto
    {
        $alias = $this->structureManager->getElementById($aliasId);
        if (!$alias instanceof groupAliasElement) {
            throw new GroupAliasDataException('Group alias not found', 404);
        }

        return new GroupAliasStateDto(
            id: $aliasId,
            modifiedAt: $alias->getModifiedTimestamp(),
            fields: $this->groupAliasFieldsReader->read($alias),
            members: $this->authorshipRepository->findByElement($aliasId, EntityType::Group),
            counters: $this->groupDetailsService->getCounters($alias),
            importOrigins: $this->importOriginsReader->read($aliasId),
        );
    }
}
