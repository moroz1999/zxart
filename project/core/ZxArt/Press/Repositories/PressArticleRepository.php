<?php
declare(strict_types=1);


namespace ZxArt\Press\Repositories;

use Illuminate\Database\Connection;
use ZxArt\Helpers\AlphanumericColumnSearch;

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
        $content = $this->db->table(self::ARCHIVE_TABLE)->select('content')->where(['id' => $articleId])->value('content');
        if ($content === null){
            return;
        }
        $this->db->table(self::TABLE)->where(['id' => $articleId])->update(['content' => $content]);

    }
}