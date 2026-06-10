<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use commentElement;
use groupAliasElement;
use groupElement;
use structureManager;
use userElement;
use ZxArt\Comments\CommentAuthorDto;
use ZxArt\Comments\CommentsListDto;
use ZxArt\Comments\CommentsService;
use ZxArt\Comments\CommentsTransformer;
use ZxArt\Groups\Repositories\GroupActivityRepository;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Ratings\Dto\AuthorRatingsListDto;
use ZxArt\Ratings\Dto\RecentRatingDto;
use ZxArtItem;

readonly final class GroupActivityService
{
    public function __construct(
        private structureManager $structureManager,
        private GroupActivityRepository $activityRepository,
        private CommentsTransformer $commentsTransformer,
        private CommentsService $commentsService,
    ) {
    }

    /**
     * Paginated votes on the group's works (same shape as the author "Votes on works" panel).
     */
    public function getRatings(int $groupId, int $page, int $perPage): AuthorRatingsListDto
    {
        $this->assertGroup($groupId);
        $workIds = $this->activityRepository->getGroupWorkIds($groupId);
        if ($workIds === []) {
            return new AuthorRatingsListDto([], 1, 0, 0);
        }

        $total = $this->activityRepository->countVotes($workIds);
        $pagesAmount = $total > 0 ? (int)ceil($total / $perPage) : 1;
        $page = max(1, min($page, $pagesAmount));
        $offset = ($page - 1) * $perPage;

        $items = [];
        foreach ($this->activityRepository->findVotesPaged($workIds, $offset, $perPage) as $vote) {
            $target = $this->structureManager->getElementById($vote['elementId']);
            if (!$target instanceof ZxArtItem || $target->isVotingDenied()) {
                continue;
            }
            $user = $this->structureManager->getElementById($vote['userId'], null, true);
            if (!$user instanceof userElement) {
                continue;
            }
            $items[] = new RecentRatingDto(
                user: new CommentAuthorDto(
                    name: $this->decode((string)$user->getTitle()),
                    url: (string)$user->getUrl(),
                    badges: $user->getBadgetTypes(),
                ),
                rating: (string)$vote['value'],
                targetTitle: $this->decode((string)$target->getTitle()),
                targetUrl: (string)$target->getUrl(),
            );
        }

        return new AuthorRatingsListDto($items, $page, $pagesAmount, $total);
    }

    /**
     * Paginated comments on the group's works (same shape as the author "Comments on works" panel).
     */
    public function getComments(int $groupId, int $page, int $perPage, ?string $languageCode = null): CommentsListDto
    {
        $this->assertGroup($groupId);
        $workIds = $this->activityRepository->getGroupWorkIds($groupId);
        if ($workIds === []) {
            return new CommentsListDto([], 1, 0, 0);
        }

        $total = $this->activityRepository->countComments($workIds);
        $pagesAmount = $total > 0 ? (int)ceil($total / $perPage) : 1;
        $page = max(1, min($page, $pagesAmount));
        $offset = ($page - 1) * $perPage;

        $comments = [];
        foreach ($this->activityRepository->findCommentIdsPaged($workIds, $offset, $perPage) as $commentId) {
            $comment = $this->structureManager->getElementById($commentId);
            if (!$comment instanceof commentElement) {
                continue;
            }
            $children = $this->commentsService->getReplies((int)$comment->id, $languageCode);
            $comments[] = $this->commentsTransformer->transformToDto($comment, $children, $languageCode);
        }

        return new CommentsListDto($comments, $page, $pagesAmount, $total);
    }

    private function assertGroup(int $groupId): void
    {
        $group = $this->structureManager->getElementById($groupId);
        if (!($group instanceof groupElement) && !($group instanceof groupAliasElement)) {
            throw new ProdDetailsException('Group or alias not found', 404);
        }
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES);
    }
}
