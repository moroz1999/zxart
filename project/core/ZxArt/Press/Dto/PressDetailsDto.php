<?php

declare(strict_types=1);

namespace ZxArt\Press\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Press\Rest\PressDetailsRestDto;

#[Map(target: PressDetailsRestDto::class)]
readonly class PressDetailsDto
{
    /**
     * @param list<PressTagDto> $tags
     * @param list<PressMentionDto> $authors
     * @param list<PressMentionDto> $people
     * @param list<PressMentionDto> $groups
     * @param list<PressMentionDto> $software
     * @param list<PressMentionDto> $pictures
     * @param list<PressMentionDto> $tunes
     * @param list<PressMentionDto> $parties
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $externalLink,
        public ?string $introduction,
        public ?string $content,
        #[Map(transform: new MapCollection())]
        public array $tags,
        #[Map(transform: new MapCollection())]
        public array $authors,
        #[Map(transform: new MapCollection())]
        public array $people,
        #[Map(transform: new MapCollection())]
        public array $groups,
        #[Map(transform: new MapCollection())]
        public array $software,
        #[Map(transform: new MapCollection())]
        public array $pictures,
        #[Map(transform: new MapCollection())]
        public array $tunes,
        #[Map(transform: new MapCollection())]
        public array $parties,
        public ?PressMentionDto $publication,
    ) {
    }
}
