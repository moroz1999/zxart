<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Skills;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Skills\LoadedSkillDto;
use ZxArt\Assistant\Skills\LoadSkillAction;
use ZxArt\Assistant\Skills\SkillCatalogue;
use ZxArt\Assistant\Skills\UnknownSkillException;

final class LoadSkillActionTest extends TestCase
{
    private const string SKILLS = __DIR__ . '/Fixtures/valid';

    public function testItIsTheReadActionLoadSkill(): void
    {
        $action = new LoadSkillAction(new SkillCatalogue(self::SKILLS));

        self::assertInstanceOf(ReadAction::class, $action);
        self::assertSame('load_skill', $action->name());
        self::assertSame(['name'], Arguments::schema($action->schema())['required']);
    }

    public function testItAnswersTheInstructionsOfTheSkill(): void
    {
        self::assertEquals(new LoadedSkillDto('merge-duplicates', 'Compare the releases.'), $this->load('merge-duplicates'));
    }

    public function testAnUnknownSkillIsRefused(): void
    {
        $this->expectException(UnknownSkillException::class);

        $this->load('import');
    }

    private function load(string $name): object
    {
        $action = new LoadSkillAction(new SkillCatalogue(self::SKILLS));

        return $action->read(Arguments::parse(json_encode(['name' => $name], JSON_THROW_ON_ERROR), $action->schema()));
    }
}
