<?php
declare(strict_types=1);

namespace ZxArt\Comments;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class CommentRestDto
{
    /**
     * @param CommentRestDto[] $children
     */
    public function __construct(
        public int $id,
        public CommentAuthorDto $author,
        public string $date,
        public string $content,
        public bool $canEdit,
        public bool $canDelete,
        public ?CommentTargetDto $target = null,
        public ?int $parentId = null,
        #[Map(transform: MapCollection::class)]
        public array $children = [],
    ) {
    }
}
