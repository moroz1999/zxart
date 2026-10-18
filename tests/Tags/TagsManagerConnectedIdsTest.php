<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tags;

use DI\Container;
use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use tagsManager;

/**
 * Searches narrow works down by tag names: included tags must all be on a work,
 * excluded ones are gathered together. A name that is no tag matches no work.
 */
#[AllowMockObjectsWithoutExpectations]
class TagsManagerConnectedIdsTest extends TestCase
{
    private const int COVER_TAG_ID = 1;
    private const int DEMO_TAG_ID = 2;

    private tagsManager $tagsManager;

    protected function setUp(): void
    {
        $db = new SQLiteConnection(new PDO('sqlite::memory:'));
        // the CMS connection fetches rows as arrays
        $db->setFetchMode(PDO::FETCH_ASSOC);
        $db->getPdo()->exec('CREATE TABLE structure_links (parentStructureId INTEGER, childStructureId INTEGER, type TEXT)');
        foreach ([[self::COVER_TAG_ID, 10], [self::COVER_TAG_ID, 11], [self::DEMO_TAG_ID, 11], [self::DEMO_TAG_ID, 12]] as [$tagId, $workId]) {
            $db->table('structure_links')->insert(['parentStructureId' => $tagId, 'childStructureId' => $workId, 'type' => 'tagLink']);
        }

        $tagsByName = [
            'Cover' => (object)['id' => self::COVER_TAG_ID],
            'Demo' => (object)['id' => self::DEMO_TAG_ID],
        ];
        $this->tagsManager = $this->createPartialMock(tagsManager::class, ['getTagElementsByNames']);
        $this->tagsManager->method('getTagElementsByNames')
            ->willReturnCallback(static fn(array $names): array => array_values(array_filter(array_map(
                static fn(string $name): ?object => $tagsByName[$name] ?? null,
                $names,
            ))));
        $this->tagsManager->setContainer(new Container(['db' => $db]));
    }

    public function testIncludedTagsAreIntersected(): void
    {
        $ids = $this->tagsManager->getConnectedElementIdsByNames(['Cover', 'Demo']);

        $this->assertSame([11], $ids);
    }

    public function testUnknownIncludedTagMatchesNothing(): void
    {
        $ids = $this->tagsManager->getConnectedElementIdsByNames(['Cover', 'No such tag']);

        $this->assertSame([], $ids);
    }

    public function testExcludedTagsAreGathered(): void
    {
        $ids = $this->tagsManager->getConnectedElementIdsByNames(['Cover', 'Demo'], false);

        sort($ids);
        $this->assertSame([10, 11, 11, 12], $ids);
    }
}
