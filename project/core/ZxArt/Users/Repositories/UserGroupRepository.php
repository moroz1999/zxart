<?php

declare(strict_types=1);

namespace ZxArt\Users\Repositories;

use structureElement;
use structureManager;
use userGroupElement;
use ZxArt\Shared\StructureType;
use ZxArt\Users\Exception\UserDataException;

/**
 * Supplies user groups to the management use cases and stores them.
 *
 * @psalm-api
 */
final readonly class UserGroupRepository
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws UserDataException 400 without an id, 404 for no group of it
     */
    public function get(int $groupId): userGroupElement
    {
        if ($groupId <= 0) {
            throw new UserDataException('Missing required group id', 400);
        }
        // groups live under the admin root, outside the public URL tree, so they are
        // loaded by id rather than resolved through a path
        $group = $this->structureManager->getElementById($groupId, null, true);
        if (!$group instanceof userGroupElement) {
            throw new UserDataException('User group not found', 404);
        }

        return $group;
    }

    /**
     * A new group in the groups folder, its structure record not yet stored.
     *
     * @throws UserDataException 500 when the element cannot be made
     */
    public function create(): userGroupElement
    {
        // the folder lives under the admin root, outside the public URL tree,
        // so it is loaded by id rather than resolved through a path
        $folder = $this->structureManager->getElementByMarker(StructureType::UserGroups->value, null, true);
        if (!$folder instanceof structureElement) {
            throw new UserDataException('The user groups folder is not available', 500);
        }
        $group = $this->structureManager->createElement(StructureType::UserGroup->value, 'showForm', $folder->getId());
        if (!$group instanceof userGroupElement) {
            throw new UserDataException('The user group could not be created', 500);
        }

        return $group;
    }

    public function save(userGroupElement $group): void
    {
        $group->persistElementData();
    }

    public function delete(userGroupElement $group): void
    {
        $group->deleteElementData();
    }
}
