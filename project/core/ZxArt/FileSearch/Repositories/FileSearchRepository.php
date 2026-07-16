<?php

declare(strict_types=1);

namespace ZxArt\FileSearch\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

/**
 * Searches the file registry (each row maps a file's name/md5 to the element it
 * belongs to) by file name or md5.
 */
readonly final class FileSearchRepository
{
    private const string TABLE = 'files_registry';

    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return array<int, array{elementId: int, fileName: string, md5: string}>
     */
    public function searchByFileName(string $term, int $limit): array
    {
        return $this->mapRows(
            $this->baseQuery()
                ->where('fileName', 'like', '%' . $term . '%')
                ->orderBy('fileName', 'asc')
                ->limit($limit)
                ->get()
        );
    }

    /**
     * @return array<int, array{elementId: int, fileName: string, md5: string}>
     */
    public function searchByMd5(string $md5, int $limit): array
    {
        return $this->mapRows(
            $this->baseQuery()
                ->where('md5', '=', $md5)
                ->limit($limit)
                ->get()
        );
    }

    private function baseQuery(): Builder
    {
        return $this->db->table(self::TABLE)->select(['elementId', 'fileName', 'md5']);
    }

    /**
     * @param iterable<array-key, mixed> $rows
     * @return array<int, array{elementId: int, fileName: string, md5: string}>
     */
    private function mapRows(iterable $rows): array
    {
        $result = [];
        /** @psalm-suppress MixedAssignment — DB rows are untyped */
        foreach ($rows as $row) {
            $data = (array)$row;
            $result[] = [
                'elementId' => (int)($data['elementId'] ?? 0),
                'fileName' => (string)($data['fileName'] ?? ''),
                'md5' => (string)($data['md5'] ?? ''),
            ];
        }
        return $result;
    }
}
