<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Skills;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;

/**
 * The arguments of `load_skill`.
 */
final readonly class LoadSkillArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "name": {"type": "string", "description": "the name of the skill, as the system prompt lists it"}
            },
            "required": ["name"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public string $name,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        return new self(
            name: $arguments->requiredText('name'),
        );
    }
}
