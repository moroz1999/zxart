<?php
declare(strict_types=1);

namespace ZxArt\Comments;

readonly class CommentTargetDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
