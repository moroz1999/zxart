<?php
declare(strict_types=1);

namespace ZxArt\Comments;

readonly class CommentAuthorDto
{
    public function __construct(
        public string $name,
        public ?string $url = null,
        public array $badges = [],
    ) {
    }
}
