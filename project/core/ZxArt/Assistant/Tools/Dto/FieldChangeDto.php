<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Dto;

use ZxArt\Shared\References\EntityMentionDto;

/**
 * One field an action changes. A value that is an entity is a mention, so the
 * panel can link it; a list field shows the whole list before and after.
 *
 * @psalm-type ChangeValue = string|int|bool|EntityMentionDto|list<string|int|EntityMentionDto>|null
 *
 * @psalm-api
 */
final readonly class FieldChangeDto
{
    /**
     * @param ChangeValue $before
     * @param ChangeValue $after
     */
    public function __construct(
        public string $field,
        public string|int|bool|EntityMentionDto|array|null $before,
        public string|int|bool|EntityMentionDto|array|null $after,
    ) {
    }
}
