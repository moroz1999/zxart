<?php

declare(strict_types=1);

namespace ZxArt\Press\Rest;

/**
 * Rich DTO consumed by the Angular <zx-press-details> page. Built directly by
 * {@see \ZxArt\Press\Services\PressDetailsService} and serialised by the json
 * renderer.
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
        public array $tags,
        public array $authors,
        public array $people,
        public array $groups,
        public array $software,
        public array $pictures,
        public array $tunes,
        public array $parties,
        public ?PressMentionRestDto $publication,
    ) {
    }
}
