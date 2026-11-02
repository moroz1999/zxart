<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Skills;

/**
 * @psalm-api
 */
final readonly class LoadSkillRequestDto
{
    /**
     * @param string $name the name of the skill, as the system prompt lists it
     */
    public function __construct(
        public string $name,
    ) {
    }
}
