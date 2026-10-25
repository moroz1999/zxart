<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

use ZxArt\Shared\EntityType;

/**
 * A mention of an entity, written `[[type:id]]` in assistant messages.
 *
 * @psalm-api
 */
final readonly class EntityMentionDto
{
    public function __construct(
        public EntityType $type,
        public int $id,
    ) {
    }

    /**
     * `type:id`, as messages store their refs.
     */
    public function key(): string
    {
        return $this->type->value . ':' . $this->id;
    }
}
