<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use App\Users\CurrentUser;
use commentElement;
use CommentsHolderInterface;
use LanguagesManager;
use linksManager;
use privilegesManager;
use structureElement;
use structureManager;

class CommentsService
{
    public function __construct(
        private readonly structureManager  $structureManager,
        private readonly CurrentUser       $user,
        private readonly LanguagesManager  $languagesManager,
        private readonly privilegesManager $privilegesManager,
        private readonly linksManager      $linksManager,
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
            $parentId = 0;
            $connectedIds = $this->linksManager->getConnectedIdList($comment->id, 'commentTarget', 'child');
            foreach ($connectedIds as $connectedId) {
                $parentElement = $this->structureManager->getElementById($connectedId);
                if ($parentElement && $parentElement->structureType === 'comment') {
                    $parentId = (int)$parentElement->id;
                    break;
                }
            }
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

        $allCommentsIds = $this->linksManager->getConnectedIdList($element->getId(), 'commentTarget', 'parent');

        foreach ($allCommentsIds as $commentId) {
            $commentElement = $this->structureManager->getElementById($commentId);
            if ($commentElement && $commentElement->structureType === 'comment' && (!$approvalRequired || $commentElement->approved)) {
                $commentsList[] = $commentElement;
            }
        }

        return $commentsList;
    }

    public function addComment(int $targetId, string $content, ?string $author = null): ?CommentDto
    {
        if (!$this->user->isAuthorized()) {
            return null;
        }

        $targetElement = $this->structureManager->getElementById($targetId);
        if (!$targetElement || (!($targetElement instanceof CommentsHolderInterface) && $targetElement->structureType !== 'comment')) {
            return null;
        }

        if (!$targetElement->areCommentsAllowed()) {
            return null;
        }

        /** @var commentElement $commentElement */
        $commentElement = $this->structureManager->createElement('comment', 'show', $targetId);
        if ($commentElement) {
            $commentElement->content = $content;
            $commentElement->userId = $this->user->id;
            $commentElement->dateTime = time();
            $commentElement->targetType = $targetElement->structureType;
            $commentElement->persistElementData();

            $this->linksManager->linkElements($targetElement->id, $commentElement->id, "commentTarget");

            if ($targetElement->structureType === 'comment') {
                /** @var commentElement $targetElement */
                $initialTarget = $targetElement->getInitialTarget();
                if ($initialTarget && $initialTarget->id != $targetElement->id) {
                    $this->linksManager->linkElements($initialTarget->id, $commentElement->id, "commentTarget");
                }
            }

            if ($this->user->id) {
                $this->privilegesManager->setPrivilege($this->user->id, $commentElement->id, 'comment', 'delete', 1);
                $this->privilegesManager->setPrivilege($this->user->id, $commentElement->id, 'comment', 'publicReceive', 1);
                $this->privilegesManager->setPrivilege($this->user->id, $commentElement->id, 'comment', 'publicForm', 1);

                $this->user->refreshPrivileges();
            }

            $this->clearCommentsCache();

            return $this->transformToDto($commentElement);
        }
        return null;
    }

    public function updateComment(int $commentId, string $content): ?CommentDto
    {
        /** @var commentElement $commentElement */
        $commentElement = $this->structureManager->getElementById($commentId);
        if ($commentElement && $commentElement->structureType === 'comment') {
            if ($commentElement->isEditable() && $commentElement->userId == $this->user->id) {
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
            if ($commentElement->isEditable() && $commentElement->userId == $this->user->id) {
                // Perform recursive deletion
                $this->deleteRecursively($commentElement);
                $this->clearCommentsCache();
                return true;
            }

            if ($this->privilegesManager->checkPrivilegesForAction($commentId, 'delete', 'comment')) {
                // Perform recursive deletion
                $this->deleteRecursively($commentElement);
                $this->clearCommentsCache();
                return true;
            }
        }
        return false;
    }

    private function deleteRecursively(commentElement $element): void
    {
        foreach ($element->getReplies() as $reply) {
            $this->deleteRecursively($reply);
        }
        $element->deleteElementData();
    }

    public function clearCommentsCache(): void
    {
        if ($currentLanguageElement = $this->structureManager->getElementById($this->languagesManager->getCurrentLanguageId())) {
            if (method_exists($currentLanguageElement, 'clearCommentsCache')) {
                $currentLanguageElement->clearCommentsCache();
            }
        }
    }

    /**
     * @param CommentDto[] $children
     */
    public function transformToDto(structureElement $comment, array $children = []): CommentDto
    {
        $authorUser = null;
        if (method_exists($comment, 'getUserElement')) {
            $authorUser = $comment->getUserElement();
        }

        $badges = [];
        $url = null;
        $authorName = '';
        if (method_exists($comment, 'getAuthorName')) {
            /** @var commentElement $comment */
            $authorName = $comment->getAuthorName();
        }

        if ($authorUser) {
            if (method_exists($authorUser, 'getBadgetTypes')) {
                $badges = $authorUser->getBadgetTypes();
            } elseif (method_exists($authorUser, 'getBadgeTypes')) {
                $badges = $authorUser->getBadgeTypes();
            }
            $url = $authorUser->getUrl();
            $authorName = $authorUser->getTitle();
        }

        $authorDto = new CommentAuthorDto(
            name: $authorName,
            url: $url,
            badges: $badges,
        );

        $canEdit = method_exists($comment, 'isEditable') && $comment->isEditable() && $comment->userId == $this->user->id;
        $canDelete = $canEdit || ($this->privilegesManager->checkPrivilegesForAction($comment->id, 'delete', 'comment')
            && (!$comment instanceof \commentElement || $comment->isEditable()));

        $targetDto = null;
        if (method_exists($comment, 'getInitialTarget')) {
            if ($target = $comment->getInitialTarget()) {
                $targetDto = new CommentTargetDto(
                    title: $target->getTitle(),
                    url: $target->getUrl()
                );
            }
        }

        return new CommentDto(
            id: (int)$comment->id,
            author: $authorDto,
            date: (string)$comment->dateCreated,
            content: (string)$comment->getDecoratedContent(),
            originalContent: strip_tags((string)$comment->getValue('content')),
            canEdit: $canEdit,
            canDelete: $canDelete,
            target: $targetDto,
            parentId: (method_exists($comment, 'getParentElement') && ($parent = $comment->getParentElement()) && $parent->structureType === 'comment') ? (int)$parent->id : null,
            children: $children
        );
    }
}
