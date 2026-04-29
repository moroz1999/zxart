<?php

declare(strict_types=1);

namespace ZxArt\Press\Rest;

readonly class PressArticleRestDto
{
    /**
     * @param array<array{title: string, url: string}> $authors
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $titleHtml,
        public string $url,
        public ?string $snippetHtml,
        public ?int $year,
        public array $authors,
    ) {
    }
}
