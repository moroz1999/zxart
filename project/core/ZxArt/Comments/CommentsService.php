<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use CommentsHolderInterface;
use structureElement;
use structureManager;

class CommentsService
{
    public function __construct(
        private readonly structureManager $structureManager,
    ) {
    }

    /**
     * @return CommentDto[]
     */
    public function getCommentsTree(int $elementId): array
    {
        $comments = $this->getCommentsList($elementId);
        if (empty($comments)) {
            return [];
        }

        $commentsByParent = [];
        foreach ($comments as $comment) {
            $parentId = $comment->parentId ? (int)$comment->parentId : 0;
            $commentsByParent[$parentId][] = $comment;
        }

        return $this->buildTree($commentsByParent, 0);
    }

    /**
     * @param array<int, structureElement[]> $commentsByParent
     * @return CommentDto[]
     */
    private function buildTree(array $commentsByParent, int $parentId): array
    {
        $tree = [];
        if (isset($commentsByParent[$parentId])) {
            foreach ($commentsByParent[$parentId] as $comment) {
                $children = $this->buildTree($commentsByParent, (int)$comment->id);
                $tree[] = $this->transformToDto($comment, $children);
            }
        }
        return $tree;
    }

    /**
     * @return structureElement[]
     */
    public function getCommentsList(int $elementId): array
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element || !($element instanceof CommentsHolderInterface)) {
            return [];
        }

        $commentsList = [];
        $approvalRequired = false;

        $comments = $this->structureManager->getElementsChildren($element->getId(), 'content', 'commentTarget');
        foreach ($comments as $commentElement) {
            if (!$approvalRequired || $commentElement->approved) {
                $commentsList[] = $commentElement;
            }
        }

        return $commentsList;
    }

    /**
     * @param CommentDto[] $children
     */
    public function transformToDto(structureElement $comment, array $children = []): CommentDto
    {
        $author = new CommentAuthorDto(
            name: (string)$comment->author,
            url: method_exists($comment, 'getAuthorUrl') ? $comment->getAuthorUrl() : null,
            badge: property_exists($comment, 'authorBadge') ? (string)$comment->authorBadge : null,
        );

        return new CommentDto(
            id: (int)$comment->id,
            author: $author,
            date: (string)$comment->dateCreated,
            content: (string)$comment->content,
            parentId: $comment->parentId ? (int)$comment->parentId : null,
            children: $children
        );
    }
}
