<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Rest;

final readonly class MessageRestDto
{
    /**
     * @param array<array-key, mixed>|null $payload
     */
    public function __construct(
        public int $id,
        public int $seq,
        public string $role,
        public string $kind,
        public string $text,
        public ?array $payload,
        public int $createdAt,
    ) {
    }
}
