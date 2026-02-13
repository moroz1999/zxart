<?php
declare(strict_types=1);

namespace ZxArt\Comments;

readonly class CommentDto
{
    /**
     * @param CommentDto[] $children
     */
    public function __construct(
        public int $id,
        public ?CommentAuthorDto $author,
        public string $date,
        public string $content,
        public string $originalContent,
        public bool $canEdit,
        public bool $canDelete,
        public ?CommentTargetDto $target = null,
        public ?int $parentId = null,
        public array $children = [],
    ) {
    }
}
