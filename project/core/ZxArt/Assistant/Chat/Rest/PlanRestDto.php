<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Rest;

final readonly class PlanRestDto
{
    /**
     * @param list<array<array-key, mixed>> $actions
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $status,
        public array $actions,
        public int $createdAt,
        public ?int $approvedAt,
        public ?int $finishedAt,
    ) {
    }
}
