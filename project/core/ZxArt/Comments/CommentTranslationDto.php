<?php
declare(strict_types=1);

namespace ZxArt\Comments;

final readonly class CommentTranslationDto
{
    public function __construct(
        public string $textEn,
        public string $textRu,
        public string $textEs,
        public string $originalLanguageCode,
    ) {
    }
}
