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
        #[Map]
        public ?CommentAuthorDto $author,
        public string $date,
        public string $content,
        public string $originalContent,
        public bool $canEdit,
        public bool $canDelete,
        #[Map]
        public ?CommentTargetDto $target = null,
        public ?int $parentId = null,
        #[Map(transform: MapCollection::class)]
        public array $children = [],
    ) {
    }
}
