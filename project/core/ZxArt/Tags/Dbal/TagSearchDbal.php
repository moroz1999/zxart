<?php

declare(strict_types=1);

namespace ZxArt\Tags\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Search\Dbal\TitleMatch;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Tags\Dto\FoundTagDto;

/**
 * Tags by a part of the name or of a synonym in any language, the most used
 * first. A tag is stored in a row per language; each comes once, named by the
 * row that matched.
 *
 * @psalm-api
 */
final readonly class TagSearchDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
        private TitleMatch $titleMatch,
    ) {
    }

    /**
     * @return list<FoundTagDto>
     */
    public function find(string $phrase, int $limit): array
    {
        $phrase = trim($phrase);
        if ($phrase === '') {
            return [];
        }
        /** @var int[] $ids */
        $ids = $this->matching($phrase)
            ->select(['id', 'amount'])
            // a tag's every language row holds the same amount
            ->groupBy('id', 'amount')
            ->orderBy('amount', 'desc')
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id');
        if ($ids === []) {
            return [];
        }
        /** @var list<array{id: int, title: string, synonym: string, amount: int}> $rows */
        $rows = $this->matching($phrase)
            ->whereIn('id', $ids)
            ->orderBy('languageId')
            ->get(['id', 'title', 'synonym', 'amount']);
        $found = [];
        foreach ($rows as $row) {
            $found[(int)$row['id']] ??= new FoundTagDto((int)$row['id'], $row['title'], $row['synonym'], (int)$row['amount']);
        }

        return array_values(array_map(static fn(int $id): FoundTagDto => $found[(int)$id], $ids));
    }

    private function matching(string $phrase): Builder
    {
        return $this->titleMatch->where($this->db->table($this->tableName(DatabaseTable::Tag)), ['title', 'synonym'], $phrase);
    }
}
