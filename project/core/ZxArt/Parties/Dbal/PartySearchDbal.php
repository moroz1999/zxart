<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Parties\Dto\FoundPartyDto;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;

/**
 * Parties by a part of the title, the abbreviation or the original name, of
 * one year or any. The year of a party is the title of the year element it
 * lies under. Titles are stored HTML-encoded, so a phrase matches as typed and
 * as encoded, and what is answered is decoded.
 *
 * @psalm-api
 */
final readonly class PartySearchDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return list<FoundPartyDto> by title
     */
    public function find(string $phrase, ?int $year, int $limit): array
    {
        $phrase = trim($phrase);
        if ($phrase === '') {
            return [];
        }
        // a backslash escapes the wildcards in MariaDB
        $patterns = array_unique(array_map(
            static fn(string $text): string => '%' . addcslashes($text, '\\%_') . '%',
            [$phrase, htmlspecialchars($phrase, ENT_QUOTES)],
        ));
        $query = $this->db->table($this->tableName(DatabaseTable::Party))
            ->where(static function (Builder $query) use ($patterns): void {
                foreach ($patterns as $pattern) {
                    foreach (['title', 'abbreviation', 'originalName'] as $column) {
                        $query->orWhere($column, 'like', $pattern);
                    }
                }
            });
        if ($year !== null) {
            $query->whereIn('id', $this->yearLinks()->select('childStructureId')->where($this->tableColumn(DatabaseTable::Generic, 'title'), '=', (string)$year));
        }
        /** @var list<array{id: int, title: string, abbreviation: string}> $rows */
        $rows = $query->orderBy('title')->orderBy('id')->limit($limit)->get(['id', 'title', 'abbreviation']);
        $years = $this->yearsOf(array_column($rows, 'id'));

        return array_map(fn(array $row): FoundPartyDto => new FoundPartyDto(
            $row['id'],
            $this->decode($row['title']),
            $this->decode($row['abbreviation']),
            $years[$row['id']] ?? null,
        ), $rows);
    }

    /**
     * @param list<int> $ids
     * @return array<int, int> the year by party id, for the parties under a year
     */
    private function yearsOf(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        /** @var list<array{id: int, year: string}> $rows */
        $rows = $this->yearLinks()
            ->whereIn('childStructureId', $ids)
            ->get(['childStructureId as id', $this->tableColumn(DatabaseTable::Generic, 'title') . ' as year']);
        $years = [];
        foreach ($rows as $row) {
            if (is_numeric($row['year'])) {
                $years[$row['id']] = (int)$row['year'];
            }
        }

        return $years;
    }

    /** The structure links of year elements to what lies under them. */
    private function yearLinks(): Builder
    {
        $links = $this->tableName(DatabaseTable::StructureLinks);

        return $this->db->table($links)
            ->join($this->tableName(DatabaseTable::Generic), $this->tableColumn(DatabaseTable::Generic, 'id'), '=', $links . '.parentStructureId')
            ->where($links . '.type', '=', 'structure');
    }

    private function decode(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
