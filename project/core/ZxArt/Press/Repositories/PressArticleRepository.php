<?php
declare(strict_types=1);


namespace ZxArt\Press\Repositories;

use Illuminate\Database\Connection;

final class PressArticleRepository
{
    public const TABLE = 'module_pressarticle';
    public const ARCHIVE_TABLE = 'module_pressarticle_archive';

    public function __construct(
        private readonly Connection $db,
    )
    {

    }

    public function restoreContent(int $articleId): void
    {
        $content = $this->db->table(self::ARCHIVE_TABLE)->select('content')->limit(1)->where(['id' => $articleId])->value('content');
        if ($content === null) {
            return;
        }
        $this->db
            ->table(self::TABLE)
            ->where(['id' => $articleId])
            ->update(['content' => $content]);
    }

    public function getOriginalContent(int $articleId): ?string
    {
        return $this->db
            ->table(self::ARCHIVE_TABLE)
            ->select('content')
            ->limit(1)
            ->where(['id' => $articleId])
            ->value('content');
    }

    public function saveOriginalContent(int $articleId, string $content): void
    {
        $exists = $this->db->table(self::ARCHIVE_TABLE)->where(['id' => $articleId])->exists();

        if ($exists) {
            $this->db->table(self::ARCHIVE_TABLE)
                ->where(['id' => $articleId])
                ->update(['content' => $content]);
        } else {
            $this->db->table(self::ARCHIVE_TABLE)
                ->insert([
                    'id' => $articleId,
                    'content' => $content
                ]);
        }
    }
}