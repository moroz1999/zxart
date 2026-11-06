<?php

declare(strict_types=1);

namespace ZxArt\Users\Repositories;

use structureManager;
use userElement;
use ZxArt\Users\Exception\UserDataException;

/**
 * Supplies users to the management use cases and stores them.
 *
 * @psalm-api
 */
final readonly class UserRepository
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws UserDataException 400 without an id, 404 for no user of it
     */
    public function get(int $userId): userElement
    {
        if ($userId <= 0) {
            throw new UserDataException('Missing required user id', 400);
        }
        // users live under the admin root, outside the public URL tree, so they are
        // loaded by id rather than resolved through a path
        $user = $this->structureManager->getElementById($userId, null, true);
        if (!$user instanceof userElement) {
            throw new UserDataException('User not found', 404);
        }

        return $user;
    }

    public function save(userElement $user): void
    {
        $user->persistElementData();
    }

    /**
     * Moves the modification time after a change kept outside the element.
     */
    public function touch(userElement $user): void
    {
        $user->persistModificationTime();
    }
}
