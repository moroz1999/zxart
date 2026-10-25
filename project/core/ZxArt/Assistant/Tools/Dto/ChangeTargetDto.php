<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Dto;

use ZxArt\Shared\EntityType;

/**
 * The entity an action changes: an existing one by id, or one an earlier step
 * of the same plan creates.
 *
 * @psalm-api
 */
final readonly class ChangeTargetDto
{
    /**
     * @param ?int $id null for an entity the plan creates
     * @param ?int $createdInStep the plan step that creates it, numbered from 1
     */
    public function __construct(
        public EntityType $type,
        public ?int $id,
        public string $title,
        public ?int $createdInStep = null,
    ) {
    }
}
