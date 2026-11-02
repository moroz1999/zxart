<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Skills;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Skills\SkillCatalogue;
use ZxArt\Assistant\Skills\UnknownSkillException;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\Skills\LoadSkillRequestDto;
use ZxArt\Assistant\Tools\Skills\LoadSkillTool;
use ZxArt\Assistant\Tools\Skills\LoadedSkillDto;

final class LoadSkillToolTest extends TestCase
{
    private const string SKILLS = __DIR__ . '/../../Skills/Fixtures/valid';

    public function testItIsTheReadToolLoadSkill(): void
    {
        $tool = new LoadSkillTool(new SkillCatalogue(self::SKILLS));

        self::assertInstanceOf(ReadTool::class, $tool);
        self::assertSame('load_skill', $tool->name());
        self::assertSame(LoadSkillRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersTheInstructionsOfTheSkill(): void
    {
        self::assertEquals(
            new LoadedSkillDto('merge-duplicates', 'Compare the releases.'),
            (new LoadSkillTool(new SkillCatalogue(self::SKILLS)))->execute(new LoadSkillRequestDto('merge-duplicates')),
        );
    }

    public function testAnUnknownSkillIsRefused(): void
    {
        $this->expectException(UnknownSkillException::class);

        (new LoadSkillTool(new SkillCatalogue(self::SKILLS)))->execute(new LoadSkillRequestDto('import'));
    }
}
