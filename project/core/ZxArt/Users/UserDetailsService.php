<?php

declare(strict_types=1);

namespace ZxArt\Users;

use structureElement;
use structureManager;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Users\Dbal\UserActivityDbal;
use ZxArt\Users\Dbal\UserGroupsDbal;
use ZxArt\Users\Dto\UserDetailsDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\Repositories\UserRepository;

/**
 * A user as the management form shows them: the editable fields, the groups,
 * the author they are linked to and how much they have commented and voted.
 *
 * @psalm-api
 */
final readonly class UserDetailsService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserGroupsDbal $userGroupsDbal,
        private UserActivityDbal $userActivityDbal,
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws UserDataException 400 without an id, 404 for no user of it
     */
    public function getDetails(int $userId): UserDetailsDto
    {
        $user = $this->userRepository->get($userId);
        $authorId = ElementFields::number($user, 'authorId');

        return new UserDetailsDto(
            id: $userId,
            userName: ElementFields::text($user, 'userName'),
            email: ElementFields::text($user, 'email'),
            dateCreated: $user->getCreatedTimestamp(),
            verified: ElementFields::flag($user, 'verified'),
            banned: ElementFields::flag($user, 'banned'),
            commentsAllowed: ElementFields::flag($user, 'comments'),
            showEmail: ElementFields::flag($user, 'showemail'),
            vip: ElementFields::flag($user, 'vip'),
            supporter: ElementFields::flag($user, 'supporter'),
            volunteer: ElementFields::flag($user, 'volunteer'),
            authorId: $authorId,
            authorTitle: $this->authorTitle($authorId),
            groupIds: $this->userGroupsDbal->getGroupIdsOfUsers([$userId])[$userId] ?? [],
            commentsCount: $this->userActivityDbal->countComments($userId),
            votesCount: $this->userActivityDbal->countVotes($userId),
        );
    }

    private function authorTitle(int $authorId): string
    {
        if ($authorId <= 0) {
            return '';
        }
        $author = $this->structureManager->getElementById($authorId);

        return $author instanceof structureElement ? html_entity_decode((string)$author->getTitle(), ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
    }
}
