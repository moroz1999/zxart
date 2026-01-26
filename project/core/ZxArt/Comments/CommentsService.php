<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use App\Logging\EventsLog;
use commentElement;
use CommentsHolderInterface;
use controller;
use LanguagesManager;
use structureElement;
use structureManager;
use user;

class CommentsService
{
    public function __construct(
        private readonly structureManager $structureManager,
        private readonly user $user,
        private readonly LanguagesManager $languagesManager,
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

    public function addComment(int $targetId, string $content, string $author = null): ?CommentDto
    {
        $targetElement = $this->structureManager->getElementById($targetId);
        if (!$targetElement || !($targetElement instanceof CommentsHolderInterface)) {
            return null;
        }

        /** @var commentElement $commentElement */
        $commentElement = $this->structureManager->createElement('comment', 'show', $targetId);
        if ($commentElement) {
            $commentElement->content = $content;
            $commentElement->author = $author ?: ($this->user->userName ?: 'anonymous');
            $commentElement->userId = $this->user->id;
            $commentElement->dateTime = time();
            $commentElement->targetType = $targetElement->structureType;
            $commentElement->approved = 1;
            $commentElement->persistElementData();

            $linksManager = controller::getInstance()->getService('linksManager');
            $linksManager->linkElements($targetId, $commentElement->id, "commentTarget");

            if ($commentsElementId = $this->structureManager->getElementIdByMarker('comments')) {
                $this->structureManager->moveElement($targetId, $commentsElementId, $commentElement->id);
            }

            if ($currentLanguageElement = $this->structureManager->getElementById($this->languagesManager->getCurrentLanguageId())) {
                if (method_exists($currentLanguageElement, 'clearCommentsCache')) {
                    $currentLanguageElement->clearCommentsCache();
                }
            }

            $commentElement->getService(EventsLog::class)->logEvent($commentElement->id, 'comment');

            return $this->transformToDto($commentElement);
        }
        return null;
    }

    public function updateComment(int $commentId, string $content): ?CommentDto
    {
        /** @var commentElement $commentElement */
        $commentElement = $this->structureManager->getElementById($commentId);
        if ($commentElement && $commentElement->structureType === 'comment') {
            if ($commentElement->isEditable()) {
                $commentElement->content = $content;
                $commentElement->persistElementData();
                return $this->transformToDto($commentElement);
            }
        }
        return null;
    }

    public function deleteComment(int $commentId): bool
    {
        /** @var commentElement $commentElement */
        $commentElement = $this->structureManager->getElementById($commentId);
        if ($commentElement && $commentElement->structureType === 'comment') {
            $privilegesManager = controller::getInstance()->getService('privilegesManager');
            if ($privilegesManager->getPrivilege($this->user->id, $commentId, 'comment', 'delete')) {
                return $commentElement->deleteElementData();
            }
        }
        return false;
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
