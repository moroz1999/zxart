<?php

declare(strict_types=1);

namespace ZxArt\Shared\Elements;

use App\Users\CurrentUserService;
use privilegesManager;

/**
 * Lets the current user keep working on an element they have just created: the
 * actions listed are allowed to them on that element (and, by inheritance, on
 * what is created below it). Whoever grants refreshes the user's privileges once
 * it is done creating.
 *
 * @psalm-api
 */
final readonly class CreatorPrivilegesGranter
{
    public function __construct(
        private privilegesManager $privilegesManager,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @param list<array{string, string}> $privileges structure type, action
     */
    public function grant(int $elementId, array $privileges): void
    {
        $userId = (int)$this->currentUserService->getCurrentUser()->id;
        foreach ($privileges as [$structureType, $action]) {
            $this->privilegesManager->setPrivilege($userId, $elementId, $structureType, $action, 'allow');
        }
    }
}
