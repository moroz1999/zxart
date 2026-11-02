<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Skills;

/**
 * Instructions for one kind of task, which the assistant loads before it starts
 * such a task.
 *
 * @psalm-api
 */
final readonly class SkillDto
{
    /**
     * @param string $name what the assistant loads it by, the name of its file
     * @param string $description when to load it, as the system prompt lists it
     * @param string $instructions the Markdown the assistant follows
     */
    public function __construct(
        public string $name,
        public string $description,
        public string $instructions,
    ) {
    }
}
