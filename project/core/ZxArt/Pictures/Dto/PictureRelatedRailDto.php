<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureRelatedRailRestDto;

#[Map(target: PictureRelatedRailRestDto::class)]
readonly class PictureRelatedRailDto
{
    /**
     * @param PictureDto[] $items
     */
    public function __construct(
        public string $kind,
        public string $title,
        public ?string $kicker,
        public array $items,
    ) {
    }
}
