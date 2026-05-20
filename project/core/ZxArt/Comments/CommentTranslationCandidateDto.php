<?php
declare(strict_types=1);

namespace ZxArt\Comments;

final readonly class CommentTranslationCandidateDto
{
    public function __construct(
        public int $id,
        public string $text,
    ) {
    }
}
