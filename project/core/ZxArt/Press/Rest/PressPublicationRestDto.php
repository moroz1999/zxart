<?php

declare(strict_types=1);

namespace ZxArt\Press\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

/** The issue a press article was published in, with the rest of its table of contents. */
readonly class PressPublicationRestDto
{
    /**
     * @param PressMentionRestDto[] $articles
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?int $year,
        public ?string $imageUrl,
        #[Map(transform: MapCollection::class)]
        public array $articles,
    ) {
    }
}
