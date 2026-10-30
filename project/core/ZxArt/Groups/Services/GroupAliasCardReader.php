<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use ZxArt\Authors\Services\MembershipsReader;
use ZxArt\Groups\Dto\GroupAliasCardDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * @psalm-api
 */
final readonly class GroupAliasCardReader
{
    public function __construct(
        private GroupAliasRepository $groupAliasRepository,
        private GroupAliasFieldsReader $groupAliasFieldsReader,
        private MembershipsReader $membershipsReader,
        private GroupDetailsService $groupDetailsService,
        private EntityRefsReader $entityRefsReader,
    ) {
    }

    /**
     * @throws GroupAliasDataException for an id of no group alias
     */
    public function read(int $aliasId): GroupAliasCardDto
    {
        $alias = $this->groupAliasRepository->get($aliasId);
        $fields = $this->groupAliasFieldsReader->read($alias);

        return new GroupAliasCardDto(
            id: $aliasId,
            fields: $fields,
            refs: $this->entityRefsReader->read([$fields->groupId ?? 0]),
            members: $this->membershipsReader->membersOf($aliasId),
            counters: $this->groupDetailsService->getCounters($alias),
        );
    }
}
