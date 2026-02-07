<?php
declare(strict_types=1);

namespace ZxArt\Comments;

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
        private privilegesManager $privilegesManager,
    ) {
    }

    /**
     * Transforms a commentElement object into CommentDto.
     *
     * @param commentElement $comment Comment object
     * @param CommentDto[] $children List of child comments (already transformed)
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

        $hasEditPrivilege = $this->privilegesManager->checkPrivilegesForAction((int)$comment->id, 'publicReceive', 'comment');
        $isEditable = $comment->isEditable();

        $canEdit = $isEditable && $hasEditPrivilege;

        $hasDeletePrivilege = $this->privilegesManager->checkPrivilegesForAction((int)$comment->id, 'delete', 'comment');
        $canDelete = $isEditable && $hasDeletePrivilege;

        $targetDto = null;
        $target = $comment->getInitialTarget();
        if ($target instanceof structureElement) {
            $targetDto = $this->buildTargetDto($target);
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

    private function buildTargetDto(structureElement $target): CommentTargetDto
    {
        $type = (string)$target->structureType;
        $imageUrl = null;
        $authorName = null;

        if ($type === 'zxPicture') {
            /** @var \zxPictureElement $target */
            $imageUrl = $target->getImageUrl(1);
            $authorName = $this->getTargetAuthorName($target);
        } elseif ($type === 'zxProd') {
            /** @var \zxProdElement $target */
            $imageUrl = $target->getImageUrl(0, 'prodImage');
            $authorName = $this->getTargetAuthorName($target);
        } elseif ($type === 'zxRelease') {
            /** @var \zxReleaseElement $target */
            $imageUrl = $target->getImageUrl(0, 'prodImage');
            $authorName = $this->getTargetAuthorName($target);
        } elseif ($type === 'zxMusic') {
            /** @var \zxMusicElement $target */
            $authorName = $this->getTargetAuthorName($target);
        }

        return new CommentTargetDto(
            title: (string)$target->getTitle(),
            url: $target->getUrl(),
            type: $type,
            imageUrl: $imageUrl,
            authorName: $authorName,
        );
    }

    private function getTargetAuthorName(structureElement $target): ?string
    {
        if (method_exists($target, 'getAuthorNamesString')) {
            $names = $target->getAuthorNamesString();
            return $names !== '' ? $names : null;
        }
        return null;
    }
}
