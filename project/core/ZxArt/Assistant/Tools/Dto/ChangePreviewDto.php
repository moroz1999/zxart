<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Dto;

/**
 * What a plan shows for one write action before the user approves it.
 *
 * @psalm-api
 */
final readonly class ChangePreviewDto
{
    /**
     * @param FieldChangeDto[] $changes only the fields that change
     * @param string[] $consequences what else the action does, in sentences the user must see
     * @param ?int $modifiedAt the target's modification time, to tell at execution whether it changed since
     */
    public function __construct(
        public ChangeKind $kind,
        public ChangeTargetDto $target,
        public array $changes,
        public array $consequences,
        public ?int $modifiedAt,
    ) {
    }
}
