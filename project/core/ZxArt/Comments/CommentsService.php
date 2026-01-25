<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use CommentsHolderInterface;
use structureElement;
use structureManager;

class CommentsService
{
    private structureManager $structureManager;

    public function setStructureManager(structureManager $structureManager): void
    {
        $this->structureManager = $structureManager;
    }

    /**
     * @return CommentRestDto[]
     */
    public function getCommentsTree(int $elementId): array
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element || !($element instanceof CommentsHolderInterface)) {
            return [];
        }

        $comments = $element->getCommentsList();
        if (empty($comments)) {
            return [];
        }

        /** @var CommentRestDto[] $dtos */
        $dtos = [];
        /** @var structureElement $comment */
        foreach ($comments as $comment) {
            $dtos[$comment->id] = $this->transformToDto($comment);
        }

        $tree = [];
        foreach ($dtos as $dto) {
            if ($dto->parentId && isset($dtos[$dto->parentId])) {
                $dtos[$dto->parentId]->children[] = $dto;
            } else {
                $tree[] = $dto;
            }
        }

        return $tree;
    }

    public function transformToDto(structureElement $comment): CommentRestDto
    {
        $dto = new CommentRestDto();
        $dto->id = (int)$comment->id;
        $dto->author = $comment->author;
        $dto->authorUrl = method_exists($comment, 'getAuthorUrl') ? $comment->getAuthorUrl() : null;
        $dto->authorBadge = property_exists($comment, 'authorBadge') ? $comment->authorBadge : null;
        $dto->date = $comment->dateCreated;
        $dto->content = $comment->content;
        $dto->votes = (int)$comment->votes;
        $dto->votingDenied = (bool)$comment->votingDenied;
        $dto->parentId = $comment->parentId ? (int)$comment->parentId : null;
        
        // В будущем здесь можно добавить проверку на возможность комментирования
        $dto->commentsAllowed = true; 

        return $dto;
    }
}
