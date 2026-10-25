<?php

declare(strict_types=1);

namespace ZxArt\Groups\Repositories;

use groupAliasElement;
use structureManager;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Services\GroupsService;

/**
 * Supplies group alias to the use cases and stores them: every write of
 * a group alias's own data goes through `save()`.
 *
 * @psalm-api
 */
final readonly class GroupAliasRepository
{
    public function __construct(
        private structureManager $structureManager,
        private GroupsService $groupsService,
    ) {
    }

    /**
     * @throws GroupAliasDataException 400 without an id, 404 for no group alias of it
     */
    public function get(int $aliasId): groupAliasElement
    {
        if ($aliasId <= 0) {
            throw new GroupAliasDataException('Missing required group alias id', 400);
        }
        $alias = $this->structureManager->getElementById($aliasId);
        if (!$alias instanceof groupAliasElement) {
            throw new GroupAliasDataException('Group alias not found', 404);
        }

        return $alias;
    }

    /**
     * A new group alias in the catalogue folder of its name's first letter, its
     * structure record not yet stored.
     *
     * @throws GroupAliasDataException 500 when the element cannot be made
     */
    public function create(string $name): groupAliasElement
    {
        $alias = $this->groupsService->manufactureAliasElement($name);
        if ($alias === null) {
            throw new GroupAliasDataException('The element could not be created', 500);
        }

        return $alias;
    }

    public function save(groupAliasElement $alias): void
    {
        $alias->persistElementData();
    }

    /**
     * Moves the modification time after a change kept outside the element.
     */
    public function touch(groupAliasElement $alias): void
    {
        $alias->persistModificationTime();
    }

    public function delete(groupAliasElement $alias): void
    {
        $alias->deleteElementData();
    }
}
