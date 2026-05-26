<?php

declare(strict_types=1);

namespace ZxArt\Comments\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Comments\CommentTranslationCandidateDto;
use ZxArt\Comments\CommentTranslationDto;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class CommentsRepository extends AbstractRepository
{
    private const string TABLE = 'structure_elements';

    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * Returns the total number of comments.
     */
    public function countAll(): int
    {
        return $this->getCommentQuery()->count('id');
    }

    /**
     * Returns comment IDs for a given page, sorted by date descending.
     *
     * @return int[]
     */
    public function getIdsPaginated(int $offset, int $limit): array
    {
        $rows = $this->getCommentQuery()
            ->orderBy('dateCreated', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->select('id')
            ->get();

        return array_map(static fn(array $row): int => (int)$row['id'], $rows);
    }

    /**
     * Returns the latest comment IDs sorted by date descending.
     *
     * @return int[]
     */
    public function getLatestIds(int $limit): array
    {
        $rows = $this->getCommentQuery()
            ->where('dateCreated', '<=', time())
            ->orderBy('dateCreated', 'desc')
            ->limit($limit)
            ->select('id')
            ->get();

        return array_map(static fn(array $row): int => (int)$row['id'], $rows);
    }

    /**
     * @return CommentTranslationCandidateDto[]
     */
    public function getUntranslatedComments(int $limit): array
    {
        $rows = $this->db->table($this->tableName(DatabaseTable::Comment))
            ->where('is_translated', '=', 0)
            ->where('content', '!=', '')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->select(['id', 'content'])
            ->get();

        return array_map(
            static fn(array $row): CommentTranslationCandidateDto => new CommentTranslationCandidateDto(
                id: (int)$row['id'],
                text: (string)$row['content'],
            ),
            $rows
        );
    }

    public function saveTranslation(int $id, CommentTranslationDto $translation): void
    {
        $this->db->table($this->tableName(DatabaseTable::Comment))
            ->where('id', '=', $id)
            ->update([
                'text_en' => $translation->originalLanguageCode === 'en' ? '' : $translation->textEn,
                'text_ru' => $translation->originalLanguageCode === 'ru' ? '' : $translation->textRu,
                'text_es' => $translation->originalLanguageCode === 'es' ? '' : $translation->textEs,
                'is_translated' => 1,
            ]);
    }

    public function countByAuthorId(int $authorId): int
    {
        $authorIds = $this->getAuthorAndAliasIds($authorId);
        $workIds = $this->getAuthorWorkIds($authorIds);

        if (empty($workIds)) {
            return 0;
        }

        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        return $this->db->table($linksTable)
            ->join(self::TABLE, self::TABLE . '.id', '=', $linksTable . '.childStructureId')
            ->whereIn($linksTable . '.parentStructureId', $workIds)
            ->where($linksTable . '.type', '=', LinkTypes::COMMENT_TARGET->value)
            ->where(self::TABLE . '.structureType', '=', 'comment')
            ->count(self::TABLE . '.id');
    }

    /**
     * @return int[]
     */
    public function getIdsByAuthorId(int $authorId, int $offset, int $limit): array
    {
        $authorIds = $this->getAuthorAndAliasIds($authorId);
        $workIds = $this->getAuthorWorkIds($authorIds);

        if (empty($workIds)) {
            return [];
        }

        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        $rows = $this->db->table($linksTable)
            ->join(self::TABLE, self::TABLE . '.id', '=', $linksTable . '.childStructureId')
            ->whereIn($linksTable . '.parentStructureId', $workIds)
            ->where($linksTable . '.type', '=', LinkTypes::COMMENT_TARGET->value)
            ->where(self::TABLE . '.structureType', '=', 'comment')
            ->orderBy(self::TABLE . '.dateCreated', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->select(self::TABLE . '.id')
            ->get();

        return array_map(static fn(array $row): int => (int)$row['id'], $rows);
    }

    /**
     * @return int[]
     */
    private function getAuthorAndAliasIds(int $authorId): array
    {
        /** @var int[] $aliasIds */
        $aliasIds = $this->db->table($this->tableName(DatabaseTable::AuthorAlias))
            ->where('authorId', '=', $authorId)
            ->pluck('id');
        return [$authorId, ...$aliasIds];
    }

    /**
     * @param int[] $authorIds
     * @return int[]
     */
    private function getAuthorWorkIds(array $authorIds): array
    {
        /** @var int[] $pictureAndTuneIds */
        $pictureAndTuneIds = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->whereIn('parentStructureId', $authorIds)
            ->whereIn('type', [LinkTypes::AUTHOR_PICTURE->value, LinkTypes::AUTHOR_MUSIC->value])
            ->pluck('childStructureId');

        /** @var int[] $prodIds */
        $prodIds = $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->whereIn('authorId', $authorIds)
            ->distinct()
            ->pluck('elementId');

        return array_values(array_unique(array_merge($pictureAndTuneIds, $prodIds)));
    }

    private function getCommentQuery(): Builder
    {
        return $this->db->table(self::TABLE)
            ->where('structureType', '=', 'comment');
    }
}
