<?php
declare(strict_types=1);

namespace ZxArt\Comments;

final readonly class CommentTranslationAttemptDto
{
    public function __construct(
        public int $commentId,
        public string $sourceText,
        public ?CommentTranslationDto $translation,
        public ?string $error,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->translation !== null && $this->error === null;
    }
}
