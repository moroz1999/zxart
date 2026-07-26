<?php

declare(strict_types=1);

namespace ZxArt\Press\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

/**
 * Rich response consumed by the Angular press details page.
 */
readonly class PressDetailsRestDto
{
    /**
     * @param PressTagRestDto[]     $tags
     * @param PressMentionRestDto[] $authors
     * @param PressMentionRestDto[] $people
     * @param PressMentionRestDto[] $groups
     * @param PressMentionRestDto[] $software
     * @param PressMentionRestDto[] $pictures
     * @param PressMentionRestDto[] $tunes
     * @param PressMentionRestDto[] $parties
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $externalLink,
        public ?string $introduction,
        public ?string $content,
        #[Map(transform: MapCollection::class)]
        public array $tags,
        #[Map(transform: MapCollection::class)]
        public array $authors,
        #[Map(transform: MapCollection::class)]
        public array $people,
        #[Map(transform: MapCollection::class)]
        public array $groups,
        #[Map(transform: MapCollection::class)]
        public array $software,
        #[Map(transform: MapCollection::class)]
        public array $pictures,
        #[Map(transform: MapCollection::class)]
        public array $tunes,
        #[Map(transform: MapCollection::class)]
        public array $parties,
        public ?PressMentionRestDto $publication,
    ) {
    }
}
