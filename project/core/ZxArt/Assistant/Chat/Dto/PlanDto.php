<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Shared\ObjectMapper\BackedEnumValue;
use ZxArt\Assistant\Chat\Rest\PlanRestDto;
use ZxArt\Assistant\Chat\PlanStatus;

#[Map(target: PlanRestDto::class)]
final readonly class PlanDto
{
    /**
     * @param list<array<array-key, mixed>> $actions as stored, see {@see \ZxArt\Assistant\Chat\Plans\PlanActionDto}
     */
    public function __construct(
        public int $id,
        public int $conversationId,
        public string $title,
        #[Map(transform: new BackedEnumValue())]
        public PlanStatus $status,
        public array $actions,
        public int $createdAt,
        public ?int $approvedAt,
        public ?int $finishedAt,
    ) {
    }
}
