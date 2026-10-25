<?php

declare(strict_types=1);

namespace ZxArt\Groups\Repositories;

use groupElement;
use structureManager;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Services\GroupsService;

/**
 * Supplies groups to the use cases and stores them: every write of
 * a group's own data goes through `save()`.
 *
 * @psalm-api
 */
final readonly class GroupRepository
{
    public function __construct(
        private structureManager $structureManager,
        private GroupsService $groupsService,
    ) {
    }

    /**
     * @throws GroupDataException 400 without an id, 404 for no group of it
     */
    public function get(int $groupId): groupElement
    {
        if ($groupId <= 0) {
            throw new GroupDataException('Missing required group id', 400);
        }
        $group = $this->structureManager->getElementById($groupId);
        if (!$group instanceof groupElement) {
            throw new GroupDataException('Group not found', 404);
        }

        return $group;
    }

    /**
     * A new group in the catalogue folder of its name's first letter, its
     * structure record not yet stored.
     *
     * @throws GroupDataException 500 when the element cannot be made
     */
    public function create(string $name): groupElement
    {
        $group = $this->groupsService->manufactureGroupElement($name);
        if ($group === null) {
            throw new GroupDataException('The element could not be created', 500);
        }

        return $group;
    }

    public function save(groupElement $group): void
    {
        $group->persistElementData();
    }

    /**
     * Moves the modification time after a change kept outside the element.
     */
    public function touch(groupElement $group): void
    {
        $group->persistModificationTime();
    }

    public function delete(groupElement $group): void
    {
        $group->deleteElementData();
    }
}
