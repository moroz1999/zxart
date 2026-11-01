<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Shared\ObjectMapper\BackedEnumValue;
use ZxArt\Assistant\Chat\Rest\MessageRestDto;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;

#[Map(target: MessageRestDto::class)]
final readonly class MessageDto
{
    /**
     * @param array<array-key, mixed>|null $payload
     */
    public function __construct(
        public int $id,
        public int $conversationId,
        public int $seq,
        #[Map(transform: new BackedEnumValue())]
        public MessageRole $role,
        #[Map(transform: new BackedEnumValue())]
        public MessageKind $kind,
        public string $text,
        public ?array $payload,
        public int $createdAt,
    ) {
    }
}
