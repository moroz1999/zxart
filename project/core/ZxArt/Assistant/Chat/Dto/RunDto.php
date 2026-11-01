<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Shared\ObjectMapper\BackedEnumValue;
use ZxArt\Assistant\Chat\Rest\RunRestDto;
use ZxArt\Assistant\Chat\RunStatus;
use ZxArt\Assistant\Chat\RunType;

#[Map(target: RunRestDto::class)]
final readonly class RunDto
{
    public function __construct(
        public int $id,
        public int $conversationId,
        #[Map(transform: new BackedEnumValue())]
        public RunType $type,
        #[Map(transform: new BackedEnumValue())]
        public RunStatus $status,
        public int $steps,
        public int $tokensIn,
        public int $tokensOut,
        public ?string $error,
        public int $startedAt,
        public ?int $finishedAt,
        public bool $stopRequested,
    ) {
    }
}
