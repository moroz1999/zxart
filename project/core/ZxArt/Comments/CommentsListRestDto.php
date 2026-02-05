<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class CommentsListRestDto
{
    /**
     * @param CommentRestDto[] $comments
     */
    public function __construct(
        #[Map(transform: MapCollection::class)]
        public array $comments,
        public int $currentPage,
        public int $pagesAmount,
        public int $totalCount,
    ) {
    }
}
