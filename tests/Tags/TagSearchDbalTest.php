<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tags;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Tags\Dbal\TagSearchDbal;
use ZxArt\Tags\Dto\FoundTagDto;

/**
 * Tags by a part of their name or of a synonym in any language, the most used
 * first, each tag once.
 */
final class TagSearchDbalTest extends TestCase
{
    private const int GAME_GRAPHICS = 1;
    private const int GAMES = 2;
    private const int MUSIC = 3;

    private TagSearchDbal $dbal;

    protected function setUp(): void
    {
        $db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $db->setFetchMode(PDO::FETCH_ASSOC);
        $db->getPdo()->exec('CREATE TABLE module_tag (id INTEGER, languageId INTEGER, title TEXT, synonym TEXT, amount INTEGER)');
        $db->table('module_tag')->insert([
            ['id' => self::GAME_GRAPHICS, 'languageId' => 1, 'title' => 'Game Graphics', 'synonym' => 'Sprites, Tiles', 'amount' => 2521],
            ['id' => self::GAME_GRAPHICS, 'languageId' => 2, 'title' => 'Игровая графика', 'synonym' => 'Спрайты', 'amount' => 2521],
            ['id' => self::GAMES, 'languageId' => 1, 'title' => 'Games', 'synonym' => '', 'amount' => 3000],
            ['id' => self::MUSIC, 'languageId' => 1, 'title' => 'Music', 'synonym' => 'Tunes', 'amount' => 10],
        ]);
        $this->dbal = new TagSearchDbal($db);
    }

    public function testTheMostUsedTagsComeFirstEachOnceNamedByTheRowThatMatched(): void
    {
        self::assertEquals([
            new FoundTagDto(self::GAMES, 'Games', '', 3000),
            new FoundTagDto(self::GAME_GRAPHICS, 'Game Graphics', 'Sprites, Tiles', 2521),
        ], $this->dbal->find(' game ', 10));
    }

    public function testASynonymOrANameInAnotherLanguageFindsTheTag(): void
    {
        self::assertEquals([new FoundTagDto(self::GAME_GRAPHICS, 'Игровая графика', 'Спрайты', 2521)], $this->dbal->find('Спрайт', 10));
        self::assertEquals([new FoundTagDto(self::MUSIC, 'Music', 'Tunes', 10)], $this->dbal->find('tunes', 10));
    }

    public function testTheLimitCutsTheList(): void
    {
        self::assertSame([self::GAMES], array_map(static fn(FoundTagDto $tag): int => $tag->id, $this->dbal->find('game', 1)));
    }

    public function testABlankPhraseFindsNothing(): void
    {
        self::assertSame([], $this->dbal->find('  ', 10));
    }
}
