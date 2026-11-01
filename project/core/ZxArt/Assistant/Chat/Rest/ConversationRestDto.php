<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Rest;

final readonly class ConversationRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public int $createdAt,
        public int $lastMessageAt,
    ) {
    }
}
