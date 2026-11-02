<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Skills;

use Override;
use ZxArt\Assistant\Skills\SkillCatalogue;
use ZxArt\Assistant\Tools\ReadTool;

/**
 * @implements ReadTool<LoadSkillRequestDto>
 *
 * @psalm-api
 */
final readonly class LoadSkillTool implements ReadTool
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
    public function requestClass(): string
    {
        return LoadSkillRequestDto::class;
    }

    #[Override]
    public function execute(object $request): LoadedSkillDto
    {
        $skill = $this->skills->get($request->name);

        return new LoadedSkillDto($skill->name, $skill->instructions);
    }
}
