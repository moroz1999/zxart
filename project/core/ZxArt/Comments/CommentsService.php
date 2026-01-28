<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use App\Users\CurrentUser;
use Cache;
use commentElement;
use CommentsHolderInterface;
use LanguagesManager;
use linksManager;
use privilegesManager;
use structureElement;
use structureManager;
use userElement;

readonly class CommentsService
{
    public function __construct(
        private structureManager  $structureManager,
        private CurrentUser       $user,
        private LanguagesManager  $languagesManager,
        private privilegesManager $privilegesManager,
        private linksManager      $linksManager,
        private Cache             $cache,
    ) {
    }

    /**
     * @return CommentDto[]
     */
    public function getCommentsTree(int $elementId): array
    {
        $comments = $this->getCommentsList($elementId);
        if ($comments === []) {
            return [];
        }

        $commentsByParent = [];
        foreach ($comments as $comment) {
            $parentId = 0;
            /** @var int[] $connectedIds */
            $connectedIds = (array)$this->linksManager->getConnectedIdList((int)$comment->id, 'commentTarget', 'child');
            foreach ($connectedIds as $connectedId) {
                $parentElement = $this->structureManager->getElementById($connectedId);
                if ($parentElement instanceof commentElement && $parentElement->structureType === 'comment') {
                    $parentId = (int)$parentElement->id;
                    break;
                }
            }
            $commentsByParent[$parentId][] = $comment;
        }

        return $this->buildTree($commentsByParent, 0);
    }

    /**
     * @param array<int, commentElement[]> $commentsByParent
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
     * @return commentElement[]
     */
    public function getCommentsList(int $elementId): array
    {
        $element = $this->structureManager->getElementById($elementId);
        if ($element === null || !($element instanceof CommentsHolderInterface)) {
            return [];
        }

        $commentsList = [];
        $approvalRequired = false;

        /** @var int[] $allCommentsIds */
        $allCommentsIds = (array)$this->linksManager->getConnectedIdList($element->getId(), 'commentTarget', 'parent');

        foreach ($allCommentsIds as $commentId) {
            $commentElement = $this->structureManager->getElementById($commentId);
            if ($commentElement instanceof commentElement && ($approvalRequired === false || (int)$commentElement->approved === 1)) {
                $commentsList[] = $commentElement;
            }
        }

        return $commentsList;
    }

    public function addComment(int $targetId, string $content, ?string $author = null): ?CommentDto
    {
        if ($this->user->isAuthorized() === false) {
            return null;
        }

        $targetElement = $this->structureManager->getElementById($targetId);
        if ($targetElement === null || (!($targetElement instanceof CommentsHolderInterface) && $targetElement->structureType !== 'comment')) {
            return null;
        }

        $areCommentsAllowed = $targetElement->areCommentsAllowed();
        if ($areCommentsAllowed === false) {
            return null;
        }

        /** @var commentElement|null $commentElement */
        $commentElement = $this->structureManager->createElement('comment', 'show', $targetId);
        if ($commentElement instanceof commentElement) {
            $commentElement->content = $content;
            $commentElement->userId = (int)$this->user->id;
            $commentElement->dateTime = time();
            $commentElement->targetType = (string)$targetElement->structureType;
            $commentElement->persistElementData();

            $this->linksManager->linkElements((int)$targetElement->id, (int)$commentElement->id, "commentTarget");

            if ($targetElement->structureType === 'comment') {
                /** @var commentElement $targetElement */
                $initialTarget = $targetElement->getInitialTarget();
                if ($initialTarget instanceof structureElement && (int)$initialTarget->id !== (int)$targetElement->id) {
                    $this->linksManager->linkElements((int)$initialTarget->id, (int)$commentElement->id, "commentTarget");
                }
            }

            if ((int)$this->user->id !== 0) {
                $this->privilegesManager->setPrivilege((int)$this->user->id, (int)$commentElement->id, 'comment', 'delete', 1);
                $this->privilegesManager->setPrivilege((int)$this->user->id, (int)$commentElement->id, 'comment', 'publicReceive', 1);
                $this->privilegesManager->setPrivilege((int)$this->user->id, (int)$commentElement->id, 'comment', 'publicForm', 1);

                $this->user->refreshPrivileges();
            }

            $this->clearCommentsCache();

            return $this->transformToDto($commentElement);
        }
        return null;
    }

    public function updateComment(int $commentId, string $content): ?CommentDto
    {
        $commentElement = $this->structureManager->getElementById($commentId);
        if ($commentElement instanceof commentElement) {
            $isEditable = $commentElement->isEditable();
            $isAuthor = (int)$commentElement->userId === (int)$this->user->id;
            $hasPrivilege = $this->privilegesManager->checkPrivilegesForAction($commentId, 'publicReceive', 'comment');

            if ($isEditable === true && ($isAuthor === true || $hasPrivilege === true)) {
                $commentElement->content = $content;
                $commentElement->persistElementData();

                $this->clearCommentsCache();

                return $this->transformToDto($commentElement);
            }
        }
        return null;
    }

    public function deleteComment(int $commentId): bool
    {
        $commentElement = $this->structureManager->getElementById($commentId);
        if ($commentElement instanceof commentElement) {
            $isEditable = $commentElement->isEditable();
            $isAuthor = (int)$commentElement->userId === (int)$this->user->id;
            $hasPrivilege = $this->privilegesManager->checkPrivilegesForAction($commentId, 'delete', 'comment');

            if ($isEditable === true && ($isAuthor === true || $hasPrivilege === true)) {
                $commentElement->deleteElementData();
                $this->clearCommentsCache();
                return true;
            }
        }
        return false;
    }

    public function clearCommentsCache(): void
    {
        $currentLanguageId = $this->languagesManager->getCurrentLanguageId();
        $this->cache->delete($currentLanguageId . ':lc');
    }

    /**
     * @param CommentDto[] $children
     */
    public function transformToDto(commentElement $comment, array $children = []): CommentDto
    {
        $authorUser = $comment->getUserElement();

        $badges = [];
        $url = null;
        $authorName = (string)$comment->getAuthorName();

        if ($authorUser instanceof userElement) {
            $badges = (array)$authorUser->getBadgetTypes();
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
