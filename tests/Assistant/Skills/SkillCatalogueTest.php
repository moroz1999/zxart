<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Skills;

use LogicException;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Skills\SkillCatalogue;
use ZxArt\Assistant\Skills\SkillDto;
use ZxArt\Assistant\Skills\UnknownSkillException;

/**
 * The skills the assistant may load, read from their Markdown files.
 */
final class SkillCatalogueTest extends TestCase
{
    private const string FIXTURES = __DIR__ . '/Fixtures/';

    public function testListsEverySkillByNameWithItsDescriptionAndInstructions(): void
    {
        self::assertEquals([
            new SkillDto('import-prod', 'Importing a production from an external site.', "# Import\n\nFind the production first."),
            new SkillDto('merge-duplicates', 'Joining two entries of one production.', 'Compare the releases.'),
        ], (new SkillCatalogue(self::FIXTURES . 'valid'))->all());
    }

    public function testGivesASkillByName(): void
    {
        self::assertSame(
            'Compare the releases.',
            (new SkillCatalogue(self::FIXTURES . 'valid'))->get('merge-duplicates')->instructions,
        );
    }

    public function testAnUnknownNameIsRefusedWithTheNamesThereAre(): void
    {
        $this->expectException(UnknownSkillException::class);
        $this->expectExceptionMessage('There is no skill "import"; the skills are: import-prod, merge-duplicates');

        (new SkillCatalogue(self::FIXTURES . 'valid'))->get('import');
    }

    public function testAFileWithoutFrontmatterIsAnError(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('import-prod.md');

        (new SkillCatalogue(self::FIXTURES . 'without-frontmatter'))->all();
    }

    public function testASkillWithoutADescriptionIsAnError(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('import-prod.md');

        (new SkillCatalogue(self::FIXTURES . 'without-description'))->all();
    }

    public function testASkillIsNamedAsItsFile(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('import-prod.md');

        (new SkillCatalogue(self::FIXTURES . 'misnamed'))->all();
    }

    public function testTheSkillsOfTheProjectAreValid(): void
    {
        $skills = (new SkillCatalogue(__DIR__ . '/../../../project/assistant-skills'))->all();

        self::assertContains('import-prod', array_map(static fn(SkillDto $skill): string => $skill->name, $skills));
    }
}
