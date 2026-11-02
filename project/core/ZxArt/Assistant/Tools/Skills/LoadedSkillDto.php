<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Skills;

/**
 * @psalm-api
 */
final readonly class LoadedSkillDto
{
    public function __construct(
        public string $name,
        public string $instructions,
    ) {
    }
}
