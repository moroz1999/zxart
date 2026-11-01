<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Assistant\Chat\Rest\ConversationSummaryRestDto;

/**
 * A conversation as the list shows it.
 */
#[Map(target: ConversationSummaryRestDto::class)]
final readonly class ConversationSummaryDto
{
    public function __construct(
        public int $id,
        public string $title,
        public int $lastMessageAt,
        /** A run is working on it. */
        public bool $running,
    ) {
    }
}
