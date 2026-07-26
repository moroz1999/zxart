<?php

declare(strict_types=1);

namespace ZxArt\Playlists\Repositories;

use Illuminate\Database\Connection;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

/**
 * Content of user playlists, read straight from the `playlist` links so a list
 * of playlists can be summarised without loading every linked element.
 */
final readonly class PlaylistContentsRepository extends AbstractRepository
{
    private const string LINK_TYPE = 'playlist';

    public function __construct(private Connection $db)
    {
    }

    /**
     * @param list<int> $playlistIds
     * @return array<int, array<string, int>> playlist id => structure type => amount
     */
    public function getContentCounts(array $playlistIds): array
    {
        if ($playlistIds === []) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $records */
        $records = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->join(
                $this->tableName(DatabaseTable::StructureElements),
                $this->tableColumn(DatabaseTable::StructureElements, 'id'),
                '=',
                $this->tableColumn(DatabaseTable::StructureLinks, 'childStructureId'),
            )
            ->select([
                $this->tableColumn(DatabaseTable::StructureLinks, 'parentStructureId'),
                $this->tableColumn(DatabaseTable::StructureElements, 'structureType'),
            ])
            ->where($this->tableColumn(DatabaseTable::StructureLinks, 'type'), '=', self::LINK_TYPE)
            ->whereIn($this->tableColumn(DatabaseTable::StructureLinks, 'parentStructureId'), $playlistIds)
            ->get();

        $counts = [];
        foreach ($records as $record) {
            $playlistId = (int)$record['parentStructureId'];
            $structureType = (string)$record['structureType'];
            $counts[$playlistId][$structureType] = ($counts[$playlistId][$structureType] ?? 0) + 1;
        }

        return $counts;
    }
}
