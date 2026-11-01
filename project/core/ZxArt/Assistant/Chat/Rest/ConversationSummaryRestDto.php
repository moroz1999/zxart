<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Rest;

final readonly class ConversationSummaryRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public int $lastMessageAt,
        public bool $running,
    ) {
    }
}
