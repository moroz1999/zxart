<?php
declare(strict_types=1);

namespace ZxArt\Comments;

readonly class CommentsListDto
{
    /**
     * @param CommentDto[] $comments
     */
    public function __construct(
        public array $comments,
        public int $currentPage,
        public int $pagesAmount,
        public int $totalCount,
    ) {
    }
}
