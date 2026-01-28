<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use App\Users\CurrentUser;
use commentElement;
use privilegesManager;
use structureElement;
use userElement;

/**
 * Service for transforming comment entities into DTOs.
 */
readonly class CommentsTransformer
{
    public function __construct(
        private CurrentUser       $user,
        private privilegesManager $privilegesManager,
    ) {
    }

    /**
     * Transforms a commentElement object into CommentDto.
     *
     * @param commentElement $comment Comment object
     * @param CommentDto[] $children List of child comments (already transformed)
     * @return CommentDto
     */
    public function transformToDto(commentElement $comment, array $children = []): CommentDto
    {
        $authorUser = $comment->getUserElement();

        $badges = [];
        $url = null;
        $authorName = (string)$comment->getAuthorName();

        if ($authorUser instanceof userElement) {
            $badges = $authorUser->getBadgetTypes();
            $url = (string)$authorUser->getUrl();
            $authorName = (string)$authorUser->getTitle();
        }

        $authorDto = new CommentAuthorDto(
            name: $authorName,
            url: $url,
            badges: $badges,
        );

        $isEditable = $comment->isEditable();
        $isAuthor = $comment->userId === (int)$this->user->id;
        $canEdit = $isEditable === true && $isAuthor === true;

        $hasDeletePrivilege = $this->privilegesManager->checkPrivilegesForAction((int)$comment->id, 'delete', 'comment');
        $canDelete = $canEdit === true || ($hasDeletePrivilege === true && $isEditable === true);

        $targetDto = null;
        $target = $comment->getInitialTarget();
        if ($target instanceof structureElement) {
            $targetDto = new CommentTargetDto(
                title: (string)$target->getTitle(),
                url: $target->getUrl()
            );
        }

        $parentId = null;
        $parent = $comment->getParentElement();
        if ($parent instanceof commentElement && $parent->structureType === 'comment') {
            $parentId = (int)$parent->id;
        }

        return new CommentDto(
            id: (int)$comment->id,
            author: $authorDto,
            date: $comment->dateCreated,
            content: $comment->getDecoratedContent(),
            originalContent: strip_tags((string)$comment->getValue('content')),
            canEdit: $canEdit,
            canDelete: $canDelete,
            target: $targetDto,
            parentId: $parentId,
            children: $children
        );
    }
}
