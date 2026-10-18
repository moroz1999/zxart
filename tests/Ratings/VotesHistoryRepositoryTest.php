<?php

declare(strict_types=1);

namespace ZxArt\Tests\Ratings;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use ZxArt\Ratings\Repositories\VotesHistoryRepository;

/**
 * A deleted work takes its votes with it.
 */
class VotesHistoryRepositoryTest extends TestCase
{
    public function testDeletesTheVotesOfOneWorkOnly(): void
    {
        $db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $db->setFetchMode(PDO::FETCH_ASSOC);
        $db->getPdo()->exec('CREATE TABLE votes_history (id INTEGER PRIMARY KEY, elementId INTEGER, userId INTEGER, value INTEGER, date INTEGER, type TEXT)');
        foreach ([10, 10, 11] as $elementId) {
            $db->table('votes_history')->insert(['elementId' => $elementId, 'userId' => 1, 'value' => 5, 'date' => 1, 'type' => 'zxPicture']);
        }

        (new VotesHistoryRepository($db))->deleteByElementId(10);

        $this->assertSame([11], $db->table('votes_history')->pluck('elementId'));
    }
}
