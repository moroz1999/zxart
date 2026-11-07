<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Tags;

use controller;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Tags\Dbal\TagSearchDbal;
use ZxArt\Tags\Dto\FoundTagDto;

/**
 * Tags by a part of their name or of a synonym in any language, the most used
 * first, each tag once; the tags are written by the test, each test is rolled
 * back.
 */
final class TagSearchDbalTest extends TestCase
{
    private const int GAME_GRAPHICS = 990_000_101;
    private const int GAMES = 990_000_102;
    private const int MUSIC = 990_000_103;
    private const int LANGUAGE_ENGLISH = 2105;
    private const int LANGUAGE_RUSSIAN = 930;

    private Connection $db;
    private TagSearchDbal $dbal;

    #[\Override]
    protected function setUp(): void
    {
        $container = controller::getInstance()->getContainer();
        $this->db = $container->get(Connection::class);
        $this->dbal = $container->get(TagSearchDbal::class);
        $this->db->beginTransaction();
        $this->tag(self::GAME_GRAPHICS, self::LANGUAGE_ENGLISH, 'Qzxgame Graphics', 'Qzxsprites, Tiles', 'Pictures of games', 990_002_521);
        $this->tag(self::GAME_GRAPHICS, self::LANGUAGE_RUSSIAN, 'Игровая qzxграфика', 'Qzxспрайты', 'Картинки игр', 990_002_521);
        $this->tag(self::GAMES, self::LANGUAGE_ENGLISH, 'Qzxgames', '', '', 990_003_000);
        $this->tag(self::MUSIC, self::LANGUAGE_ENGLISH, 'Qzxmusic', 'Qzx-tunes', '', 990_000_010);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testTheMostUsedTagsComeFirstEachOnceNamedByTheRowThatMatched(): void
    {
        self::assertEquals([
            new FoundTagDto(self::GAMES, 'Qzxgames', '', 990_003_000),
            new FoundTagDto(self::GAME_GRAPHICS, 'Qzxgame Graphics', 'Qzxsprites, Tiles', 990_002_521),
        ], $this->dbal->find(' qzxgame ', 10));
    }

    public function testASynonymOrANameInAnotherLanguageFindsTheTag(): void
    {
        self::assertEquals(
            [new FoundTagDto(self::GAME_GRAPHICS, 'Игровая qzxграфика', 'Qzxспрайты', 990_002_521)],
            $this->dbal->find('qzxспрайт', 10),
        );
        self::assertEquals([new FoundTagDto(self::MUSIC, 'Qzxmusic', 'Qzx-tunes', 990_000_010)], $this->dbal->find('qzxtunes', 10));
    }

    public function testSpacesAndPunctuationDoNotMatter(): void
    {
        self::assertSame([self::GAME_GRAPHICS], $this->ids($this->dbal->find('qzx-game graphics', 10)));
    }

    public function testTheLimitCutsTheList(): void
    {
        self::assertSame([self::GAMES], $this->ids($this->dbal->find('qzxgame', 1)));
    }

    public function testABlankPhraseFindsNothing(): void
    {
        self::assertSame([], $this->dbal->find('  ', 10));
    }

    private function tag(int $id, int $languageId, string $title, string $synonym, string $description, int $amount): void
    {
        $this->db->table(DatabaseTable::Tag->value)->insert([
            'id' => $id,
            'languageId' => $languageId,
            'title' => $title,
            'synonym' => $synonym,
            'description' => $description,
            'amount' => $amount,
            'userId' => 0,
            'verified' => 0,
        ]);
    }

    /**
     * @param FoundTagDto[] $tags
     * @return int[]
     */
    private function ids(array $tags): array
    {
        return array_map(static fn(FoundTagDto $tag): int => $tag->id, $tags);
    }
}
