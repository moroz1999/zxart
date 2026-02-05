<?php
declare(strict_types=1);

namespace ZxArt\Comments;

readonly class CommentTargetDto
{
    public function __construct(
        public string $title,
        public string $url,
        public string $type = '',
        public ?string $imageUrl = null,
        public ?string $authorName = null,
    ) {
    }
}
