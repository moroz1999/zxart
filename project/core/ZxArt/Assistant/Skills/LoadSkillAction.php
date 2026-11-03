<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Skills;

use Override;
use ZxArt\Assistant\Skills\SkillCatalogue;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;

/**
 * @psalm-api
 */
final readonly class LoadSkillAction implements ReadAction
{
    public function __construct(
        private SkillCatalogue $skills,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'load_skill';
    }

    #[Override]
    public function description(): string
    {
        return 'Loads the instructions of a skill the system prompt lists. Load one before you start a task it describes, '
            . 'and follow it.';
    }

    #[Override]
    public function schema(): string
    {
        return LoadSkillArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): LoadedSkillDto
    {
        $request = LoadSkillArgumentsDto::from($arguments);
        $skill = $this->skills->get($request->name);

        return new LoadedSkillDto($skill->name, $skill->instructions);
    }
}
