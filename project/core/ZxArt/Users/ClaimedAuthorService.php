<?php

declare(strict_types=1);

namespace ZxArt\Users;

use Illuminate\Database\Connection;
use structureManager;
use userElement;
use ZxArt\Shared\DatabaseTable;

/**
 * Keeps the author claimed by an account (`user.authorId`) pointing at a live
 * author.
 *
 * The claim is a plain field on the user element, not a structure link, so
 * nothing follows it when its author is absorbed by a merge or converted into
 * a group. Reassigning through `userElement::changeConnectedAuthor()` also
 * moves the "edit my own works" privileges onto the new author.
 */
readonly class ClaimedAuthorService
{
    public function __construct(
        private structureManager $structureManager,
        private Connection $db,
    ) {
    }

    /**
     * Moves every account claiming `$authorId` onto `$newAuthorId`. Pass `0` to
     * drop the claim entirely, for authors that do not survive in any form.
     */
    public function reassign(int $authorId, int $newAuthorId): void
    {
        foreach ($this->getClaimingUserIds($authorId) as $userId) {
            // user elements are not reachable by a path walk from the root
            $userElement = $this->structureManager->getElementById($userId, null, true);
            if (!$userElement instanceof userElement) {
                continue;
            }
            $userElement->changeConnectedAuthor($newAuthorId);
            $userElement->persistElementData();
        }
    }

    /** @return list<int> */
    private function getClaimingUserIds(int $authorId): array
    {
        if ($authorId <= 0) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $records */
        $records = $this->db->table(DatabaseTable::User->value)
            ->select(['id'])
            ->where('authorId', '=', $authorId)
            ->get();

        $userIds = [];
        foreach ($records as $record) {
            $userIds[] = (int)$record['id'];
        }

        return array_values(array_unique($userIds));
    }
}
