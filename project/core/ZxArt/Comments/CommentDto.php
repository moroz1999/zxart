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
        public CommentAuthorDto $author,
        public string $date,
        public string $content,
        public ?int $parentId = null,
        public array $children = [],
    ) {
    }
}
