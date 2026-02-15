<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use App\Users\CurrentUserService;
use Cache;
use commentElement;
use CommentsHolderInterface;
use Illuminate\Database\Connection;
use LanguagesManager;
use privilegesManager;
use structureManager;
use ZxArt\Comments\Exception\CommentAccessDeniedException;
use ZxArt\Comments\Exception\CommentNotFoundException;
use ZxArt\Comments\Exception\CommentOperationException;
use ZxArt\LinkTypes;

/**
 * Service for managing comments.
 * Provides operations for creating, updating, deleting, and retrieving a comment tree.
 */
readonly class CommentsService
{
    private const int COMMENTS_PER_PAGE = 50;

    public function __construct(
        private structureManager    $structureManager,
        private CurrentUserService  $currentUserService,
        private LanguagesManager    $languagesManager,
        private privilegesManager   $privilegesManager,
        private Cache               $cache,
        private CommentsTransformer $transformer,
        private Connection          $db,
    )
    {
    }

    /**
     * Returns a paginated list of all comments sorted by date descending.
     *
     * @param int $page Page number (1-based)
     * @throws CommentOperationException
     */
    public function getAllCommentsPaginated(int $page = 1): CommentsListDto
    {
        $count = $this->db->table('structure_elements')
            ->where('structureType', '=', 'comment')
            ->count('id');

        $pagesAmount = (int)ceil($count / self::COMMENTS_PER_PAGE);

        if ($page < 1) {
            $page = 1;
        }
        if ($page > $pagesAmount && $pagesAmount > 0) {
            $page = $pagesAmount;
        }

        $offset = ($page - 1) * self::COMMENTS_PER_PAGE;

        $rows = $this->db->table('structure_elements')
            ->where('structureType', '=', 'comment')
            ->orderBy('dateCreated', 'desc')
            ->offset($offset)
            ->limit(self::COMMENTS_PER_PAGE)
            ->select('id')
            ->get();

        $comments = [];
        foreach ($rows as $row) {
            $rowArray = (array)$row;
            $id = (int)$rowArray['id'];
            $comment = $this->structureManager->getElementById($id);
            if ($comment instanceof commentElement) {
                $comments[] = $this->transformer->transformToDto($comment);
            }
        }

        return new CommentsListDto(
            comments: $comments,
            currentPage: $page,
            pagesAmount: $pagesAmount,
            totalCount: $count,
        );
    }

    /**
     * Returns a comment tree for the specified element.
     *
     * @param int $elementId Target element ID
     * @return CommentDto[]
     * @throws CommentNotFoundException If the target element is not found
     */
    public function getCommentsTree(int $elementId): array
    {
        $element = $this->structureManager->getElementById($elementId);
        if ($element === null) {
            throw new CommentNotFoundException("Target element not found: {$elementId}");
        }

        return $this->buildTree($elementId);
    }

    /**
     * Recursively builds the comment tree.
     *
     * @param int $parentId Parent element ID
     * @param int[] $visited Visited IDs to prevent infinite recursion
     * @return CommentDto[]
     * @throws CommentOperationException
     * @throws CommentOperationException
     */
    private function buildTree(int $parentId, array $visited = []): array
    {
        if (in_array($parentId, $visited, true)) {
            return [];
        }
        $visited[] = $parentId;

        $tree = [];
        $comments = $this->getCommentsList($parentId);

        foreach ($comments as $comment) {
            $children = $this->buildTree((int)$comment->id, $visited);
            $tree[] = $this->transformer->transformToDto($comment, $children);
        }

        return $tree;
    }

    /**
     * Returns a flat list of comments for an element.
     *
     * @param int $parentId Parent element ID (target or another comment)
     * @return commentElement[]
     */
    public function getCommentsList(int $parentId): array
    {
        $parentElement = $this->structureManager->getElementById($parentId);
        if ($parentElement === null) {
            return [];
        }

        $commentsList = [];
        $approvalRequired = false;

        /** @var commentElement[] $comments */
        $comments = $this->structureManager->getElementsChildren($parentId, null, LinkTypes::COMMENT_TARGET->value, 'comment');

        foreach ($comments as $commentElement) {
            if ($approvalRequired === false || $commentElement->approved === 1) {
                $commentsList[] = $commentElement;
            }
        }

        return $commentsList;
    }

    /**
     * Adds a new comment.
     *
     * @param int $targetId Target ID (element or another comment)
     * @param string $content Comment text
     * @throws CommentAccessDeniedException If user is not authorized or comments are disabled
     * @throws CommentNotFoundException If target is not found
     * @throws CommentOperationException If failed to create comment element
     */
    public function addComment(int $targetId, string $content): CommentDto
    {
        $user = $this->currentUserService->getCurrentUser();
        if ($user->isAuthorized() === false) {
            throw new CommentAccessDeniedException("User must be authorized to add comments");
        }

        if (trim($content) === '') {
            throw new CommentOperationException("Comment content cannot be empty");
        }

        $targetElement = $this->structureManager->getElementById($targetId);
        if ($targetElement === null) {
            throw new CommentNotFoundException("Target element not found: {$targetId}");
        }

        if (!($targetElement instanceof CommentsHolderInterface) && $targetElement->structureType !== 'comment') {
            throw new CommentOperationException("Target element does not support comments");
        }

        $areCommentsAllowed = $targetElement->areCommentsAllowed();
        if ($areCommentsAllowed === false) {
            throw new CommentAccessDeniedException("Comments are not allowed for this element");
        }

        /**
         * Create the comment element.
         * @var commentElement|null $commentElement
         */
        $commentElement = $this->structureManager->createElement('comment', 'show', $targetId, false, LinkTypes::COMMENT_TARGET->value);
        if (!($commentElement instanceof commentElement)) {
            throw new CommentOperationException("Failed to create comment element");
        }

        $commentElement->content = $content;
        $commentElement->userId = (int)$user->id;
        $commentElement->dateTime = time();
        $commentElement->targetType = (string)$targetElement->structureType;
        $commentElement->persistElementData();

        if ((int)$user->id !== 0) {
            $this->privilegesManager->setPrivilege((int)$user->id, (int)$commentElement->id, 'comment', 'delete', 1);
            $this->privilegesManager->setPrivilege((int)$user->id, (int)$commentElement->id, 'comment', 'publicReceive', 1);
            $this->privilegesManager->setPrivilege((int)$user->id, (int)$commentElement->id, 'comment', 'publicForm', 1);

            $user->refreshPrivileges();
        }

        $this->clearCommentsCache();

        return $this->transformer->transformToDto($commentElement);
    }

    /**
     * Updates an existing comment.
     *
     * @param int $commentId Comment ID
     * @param string $content New text
     * @throws CommentNotFoundException If comment is not found
     * @throws CommentAccessDeniedException If no permission to edit
     * @throws CommentOperationException
     */
    public function updateComment(int $commentId, string $content): CommentDto
    {
        $commentElement = $this->structureManager->getElementById($commentId);
        if (!($commentElement instanceof commentElement)) {
            throw new CommentNotFoundException("Comment not found: {$commentId}");
        }
        $user = $this->currentUserService->getCurrentUser();

        $isEditable = $commentElement->isEditable();
        $isAuthor = (int)$commentElement->userId === (int)$user->id;
        $hasPrivilege = $this->privilegesManager->checkPrivilegesForAction($commentId, 'publicForm', 'comment');

        if ($isEditable === true && ($isAuthor === true || $hasPrivilege === true)) {
            $commentElement->content = $content;
            $commentElement->persistElementData();

            $this->clearCommentsCache();

            return $this->transformer->transformToDto($commentElement);
        }

        throw new CommentAccessDeniedException("No permission to update comment: {$commentId}");
    }

    /**
     * Deletes a comment.
     *
     * @throws CommentNotFoundException If comment is not found
     * @throws CommentAccessDeniedException If no permission to delete
     */
    public function deleteComment(int $commentId): bool
    {
        $commentElement = $this->structureManager->getElementById($commentId);
        if (!($commentElement instanceof commentElement)) {
            throw new CommentNotFoundException("Comment not found: {$commentId}");
        }
        $user = $this->currentUserService->getCurrentUser();

        $isEditable = $commentElement->isEditable();
        $isAuthor = (int)$commentElement->userId === (int)$user->id;
        $hasPrivilege = $this->privilegesManager->checkPrivilegesForAction($commentId, 'delete', 'comment');

        if ($isEditable === true && ($isAuthor === true || $hasPrivilege === true)) {
            $commentElement->deleteElementData();
            $this->clearCommentsCache();
            return true;
        }

        throw new CommentAccessDeniedException("No permission to delete comment: {$commentId}");
    }

    /**
     * Returns the latest comments sorted by date descending.
     *
     * @param int $limit Maximum number of comments to return
     * @return CommentDto[]
     * @throws CommentOperationException
     * @throws CommentOperationException
     */
    public function getLatestComments(int $limit = 10): array
    {
        $rows = $this->db->table('structure_elements')
            ->where('structureType', '=', 'comment')
            ->where('dateCreated', '<=', time())
            ->orderBy('dateCreated', 'desc')
            ->limit($limit)
            ->select('id')
            ->get();

        $comments = [];
        foreach ($rows as $row) {
            $rowArray = (array)$row;
            $id = (int)$rowArray['id'];
            $comment = $this->structureManager->getElementById($id);
            if ($comment instanceof commentElement) {
                $comments[] = $this->transformer->transformToDto($comment);
            }
        }

        return $comments;
    }

    /**
     * Clears the comments cache.
     */
    public function clearCommentsCache(): void
    {
        $currentLanguageId = $this->languagesManager->getCurrentLanguageId();
        $this->cache->delete($currentLanguageId . ':lc');
    }
}
