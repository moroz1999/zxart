<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Search\Dbal\TitleMatch;
use LanguagesManager;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;

/**
 * Countries and cities whose name in any language contains a phrase, named in
 * the current language.
 *
 * @psalm-api
 */
final readonly class PlaceSearchDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
        private LanguagesManager $languagesManager,
        private TitleMatch $titleMatch,
    ) {
    }

    /**
     * @return list<array{id: int, title: string}>
     */
    public function findCountries(string $phrase, int $limit): array
    {
        $countryTable = $this->tableName(DatabaseTable::Country);
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table($countryTable)
            ->select(['id', 'title'])
            ->where('languageId', '=', $this->getCurrentLanguageId())
            ->whereIn('id', $this->idsNamed(DatabaseTable::Country, $phrase))
            ->orderBy('title')
            ->limit($limit)
            ->get();

        return array_map(
            static fn(array $row): array => ['id' => (int)$row['id'], 'title' => (string)$row['title']],
            $rows,
        );
    }

    /**
     * @return list<array{id: int, title: string, countryId: int, countryTitle: string}>
     */
    public function findCities(string $phrase, int $limit): array
    {
        $cityTable = $this->tableName(DatabaseTable::City);
        $countryTable = $this->tableName(DatabaseTable::Country);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $languageId = $this->getCurrentLanguageId();
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table($cityTable)
            ->select([
                $cityTable . '.id',
                $cityTable . '.title',
                $countryTable . '.id as countryId',
                $countryTable . '.title as countryTitle',
            ])
            ->join($linksTable, $linksTable . '.childStructureId', '=', $cityTable . '.id')
            ->join($countryTable, $countryTable . '.id', '=', $linksTable . '.parentStructureId')
            ->where($linksTable . '.type', '=', LinkTypes::STRUCTURE->value)
            ->where($countryTable . '.languageId', '=', $languageId)
            ->where($cityTable . '.languageId', '=', $languageId)
            ->whereIn($cityTable . '.id', $this->idsNamed(DatabaseTable::City, $phrase))
            ->orderBy($cityTable . '.title')
            ->limit($limit)
            ->get();

        return array_map(
            static fn(array $row): array => [
                'id' => (int)$row['id'],
                'title' => (string)$row['title'],
                'countryId' => (int)$row['countryId'],
                'countryTitle' => (string)$row['countryTitle'],
            ],
            $rows,
        );
    }

    private function idsNamed(DatabaseTable $table, string $phrase): \Closure
    {
        return function (Builder $query) use ($table, $phrase): void {
            $this->titleMatch->where($query->select('id')->from($this->tableName($table)), ['title'], $phrase);
        };
    }

    private function getCurrentLanguageId(): int
    {
        return (int)$this->languagesManager->getCurrentLanguageId();
    }
}
