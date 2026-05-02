<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\PressArticlePublicationRestDto;

#[Map(target: PressArticlePublicationRestDto::class)]
readonly class PressArticlePublicationDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?int $year,
    ) {
    }
}
