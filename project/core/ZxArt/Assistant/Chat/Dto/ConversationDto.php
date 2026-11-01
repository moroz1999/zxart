<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Assistant\Chat\Rest\ConversationRestDto;
#[Map(target: ConversationRestDto::class)]
final readonly class ConversationDto
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $title,
        public int $createdAt,
        public int $lastMessageAt,
    ) {
    }
}
