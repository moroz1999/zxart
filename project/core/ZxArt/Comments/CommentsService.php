<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use App\Logging\EventsLog;
use App\Users\CurrentUser;
use commentElement;
use CommentsHolderInterface;
use LanguagesManager;
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

    public function addComment(int $targetId, string $content, ?string $author = null): ?CommentDto
    {
        if ($this->user->userName === 'anonymous' || !$this->user->id) {
            return null;
        }

        $targetElement = $this->structureManager->getElementById($targetId);
        if (!$targetElement || !($targetElement instanceof CommentsHolderInterface)) {
            return null;
        }

        if (!$targetElement->areCommentsAllowed()) {
            return null;
        }

        /** @var commentElement $commentElement */
        $commentElement = $this->structureManager->createElement('comment', 'show', $targetId);
        if ($commentElement) {
            $commentElement->content = $content;
            $commentElement->author = $this->user->userName;
            $commentElement->userId = $this->user->id;
            $commentElement->dateTime = time();
            $commentElement->targetType = $targetElement->structureType;
            $commentElement->approved = 1;
            $commentElement->persistElementData();

            $linksManager = $commentElement->getService('linksManager');
            $linksManager->linkElements($targetId, $commentElement->id, "commentTarget");

            if ($commentsElementId = $this->structureManager->getElementIdByMarker('comments')) {
                $this->structureManager->moveElement($targetId, $commentsElementId, $commentElement->id);
            }

            $this->clearCommentsCache();

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
            if ($this->privilegesManager->checkPrivilegesForAction($commentId, 'delete', 'comment')) {
                if ($commentElement->deleteElementData()) {
                    $this->clearCommentsCache();
                    return true;
                }
            }
        }
        return false;
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
        if (property_exists($comment, 'userId') && $comment->userId) {
            $authorUser = $this->structureManager->getElementById((int)$comment->userId);
        }

        $badges = [];
        $url = null;
        if ($authorUser && method_exists($authorUser, 'getBadgetTypes')) {
            $badges = $authorUser->getBadgetTypes();
            $url = $authorUser->getUrl();
        }

        $authorDto = new CommentAuthorDto(
            name: (string)$comment->author,
            url: $url,
            badges: $badges,
        );

        $canDelete = (bool)$this->privilegesManager->checkPrivilegesForAction($comment->id, 'delete', 'comment');
        $canEdit = method_exists($comment, 'isEditable') && $comment->isEditable() && $comment->userId == $this->user->id;

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
            content: (string)$comment->content,
            canEdit: $canEdit,
            canDelete: $canDelete,
            target: $targetDto,
            parentId: $comment->parentId ? (int)$comment->parentId : null,
            children: $children
        );
    }
}
