<?php

declare(strict_types=1);

namespace ZxArt\Search\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use ZxArt\Search\Services\PhraseSearchType;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;

/**
 * The elements of one type a phrase finds in their titles (and, for the full
 * search, their texts), each once though it is stored in a row per language.
 *
 * @psalm-api
 */
final readonly class PhraseSearchDbal extends AbstractDbal
{
    /** Crack intros are productions of their own category, kept out of the search. */
    private const int CRACKTRO_CATEGORY_ID = 92171;

    public function __construct(
        private Connection $db,
        private TitleMatch $titleMatch,
        private LanguagesManager $languagesManager,
    ) {
    }

    /**
     * All the results in order, by the main title, the closest to the phrase
     * first when asked. One query: the count is the length of the list.
     *
     * @param ?int $languageId the language articles are matched in; the current one when not given
     * @return int[]
     */
    public function ids(PhraseSearchType $type, string $phrase, bool $contentMatching, bool $closestFirst, ?int $languageId): array
    {
        $idColumn = $this->tableColumn($type->table(), 'id');
        $titleColumn = $this->tableColumn($type->table(), $type->titleColumns()[0]);
        $query = $this->matching($type, $phrase, $contentMatching, $languageId)
            ->select($idColumn)
            ->groupBy($idColumn);
        if ($closestFirst) {
            $this->titleMatch->orderGroupsByCloseness($query, $titleColumn, $phrase);
        }
        $this->titleMatch->orderGroupsByTitle($query, $titleColumn);
        /** @var int[] $ids */
        $ids = $query->orderBy($idColumn)->pluck($idColumn);

        return array_map('intval', $ids);
    }

    private function matching(PhraseSearchType $type, string $phrase, bool $contentMatching, ?int $languageId): Builder
    {
        $table = $type->table();
        $titleColumns = array_map(fn(string $column): string => $this->tableColumn($table, $column), $type->titleColumns());
        $contentColumns = $contentMatching
            ? array_map(fn(string $column): string => $this->tableColumn($table, $column), $type->contentColumns())
            : [];
        // texts are stored HTML-encoded
        $contentPattern = '%' . htmlspecialchars(trim($phrase), ENT_QUOTES, 'UTF-8') . '%';

        $query = $this->db->table($this->tableName($table))
            ->where(function (Builder $matchQuery) use ($titleColumns, $contentColumns, $contentPattern, $phrase): void {
                $this->titleMatch->where($matchQuery, $titleColumns, $phrase);
                foreach ($contentColumns as $column) {
                    $matchQuery->orWhere($column, 'like', $contentPattern);
                }
            });
        if ($type === PhraseSearchType::ZxProd) {
            $query->whereNotIn($this->tableColumn($table, 'id'), $this->cracktros());
        }
        if ($type === PhraseSearchType::PressArticle) {
            // an article is translated in a row per language: it is found by the text of the language it is read in
            $query->where(
                $this->tableColumn($table, 'languageId'),
                '=',
                $languageId ?? (int)$this->languagesManager->getCurrentLanguageId(),
            );
        }

        return $query;
    }

    private function cracktros(): Builder
    {
        return $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->select('childStructureId')
            ->where('parentStructureId', '=', self::CRACKTRO_CATEGORY_ID);
    }
}
