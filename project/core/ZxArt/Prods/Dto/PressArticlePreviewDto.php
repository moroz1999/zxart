<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class PressArticlePreviewDto
{
    /**
     * @param PressArticleAuthorDto[] $authors
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public string $introduction,
        public array $authors,
        public ?PressArticlePublicationDto $publication,
    ) {
    }
}
