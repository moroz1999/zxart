<?php

declare(strict_types=1);

namespace ZxArt\Press\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Press\Rest\PressPublicationRestDto;

/** The issue a press article was published in, with the rest of its table of contents. */
#[Map(target: PressPublicationRestDto::class)]
readonly class PressPublicationDto
{
    /**
     * @param list<PressMentionDto> $articles
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?int $year,
        public ?string $imageUrl,
        #[Map(transform: new MapCollection())]
        public array $articles,
    ) {
    }
}
