<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\PressArticleAuthorRestDto;

#[Map(target: PressArticleAuthorRestDto::class)]
readonly class PressArticleAuthorDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
