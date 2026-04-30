<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class PressArticlesDto
{
    /**
     * @param PressArticlePreviewDto[] $articles
     */
    public function __construct(
        public array $articles,
    ) {
    }
}
