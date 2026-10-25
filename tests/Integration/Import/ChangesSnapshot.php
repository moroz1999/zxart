<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Import;

use Illuminate\Database\Connection;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Everything a test wrote to the database and to the upload folders, in a form
 * that does not depend on when and where it ran.
 *
 * {@see self::begin()} notes the last id of every table with an auto-increment
 * key; {@see self::capture()} then reads the rows above it, and the rows of the
 * other tables that belong to the elements created in between. It sees new rows
 * only: a change to a row that existed before the test is not part of it.
 *
 * Normalised: a new element's id is `#E<n>`, numbered in creation order,
 * wherever a value is that id or a text holds it as a number of its own (as
 * `author<id>`); the auto-increment ids of the other tables are dropped; a
 * date or time within the test's run is `#now`; a new link's position is
 * counted from its parent's last one; rows are sorted by their content.
 */
final class ChangesSnapshot
{
    /** @var array<string, string> table => auto-increment column */
    private array $autoIncrementColumns = [];
    /** @var array<string, int> table => last id before the test */
    private array $lastIds = [];
    /** @var list<string> tables without an auto-increment key */
    private array $otherTables = [];
    private int $startedAt = 0;

    public function __construct(
        private readonly Connection $db,
    ) {
    }

    public function begin(): void
    {
        $this->startedAt = time();
        $prefix = $this->db->getTablePrefix();
        $tables = $this->db->select(
            "SELECT t.TABLE_NAME AS tableName, c.COLUMN_NAME AS columnName
             FROM information_schema.TABLES t
             LEFT JOIN information_schema.COLUMNS c
               ON c.TABLE_SCHEMA = t.TABLE_SCHEMA AND c.TABLE_NAME = t.TABLE_NAME
              AND c.EXTRA LIKE '%auto_increment%'
             WHERE t.TABLE_SCHEMA = DATABASE() AND t.TABLE_TYPE = 'BASE TABLE'
             ORDER BY t.TABLE_NAME",
        );
        foreach ($tables as $table) {
            $name = substr((string)$table['tableName'], strlen($prefix));
            if ($table['columnName'] === null) {
                $this->otherTables[] = $name;
                continue;
            }
            $column = (string)$table['columnName'];
            $this->autoIncrementColumns[$name] = $column;
            $this->lastIds[$name] = (int)$this->db->table($name)->max($column);
        }
    }

    /**
     * Ids of the elements created since {@see self::begin()}, oldest first.
     *
     * @return list<int>
     */
    public function newElementIds(): array
    {
        return array_map(
            'intval',
            $this->db->table('structure_elements')
                ->where('id', '>', $this->lastIds['structure_elements'])
                ->orderBy('id')
                ->pluck('id'),
        );
    }

    /**
     * @param array<string, string> $folders name => absolute path, listed with
     *                                       each file's size and md5
     * @return array<string, mixed>
     */
    public function capture(array $folders): array
    {
        $finishedAt = time();
        $elementIds = $this->newElementIds();
        $placeholders = [];
        foreach ($elementIds as $number => $elementId) {
            $placeholders[(string)$elementId] = '#E' . ($number + 1);
        }

        $tables = [];
        foreach ($this->autoIncrementColumns as $table => $column) {
            $rows = $this->db->table($table)
                ->where($column, '>', $this->lastIds[$table])
                ->get();
            if ($table === 'structure_links') {
                $rows = array_map(fn(array $row): array => $this->relativeLinkPosition($row), $rows);
            }
            if ($table !== 'structure_elements') {
                $rows = array_map(static function (array $row) use ($column): array {
                    unset($row[$column]);
                    return $row;
                }, $rows);
            }
            $tables[$table] = $rows;
        }
        if ($elementIds !== []) {
            foreach ($this->otherTables as $table) {
                $columns = $this->db->getSchemaBuilder()->getColumnListing($table);
                $keyColumns = array_values(array_intersect(['id', 'elementId'], $columns));
                if ($keyColumns === []) {
                    continue;
                }
                $query = $this->db->table($table);
                foreach ($keyColumns as $keyColumn) {
                    $query->orWhereIn($keyColumn, $elementIds);
                }
                $tables[$table] = $query->get();
            }
        }

        $snapshot = [];
        foreach ($tables as $table => $rows) {
            if ($rows === []) {
                continue;
            }
            $normalised = array_map(
                fn(array $row): array => $this->normaliseRow($row, $placeholders, $finishedAt),
                $rows,
            );
            usort(
                $normalised,
                static fn(array $a, array $b): int => strcmp(self::encode($a), self::encode($b)),
            );
            $snapshot[$table] = $normalised;
        }
        ksort($snapshot);

        $files = [];
        foreach ($folders as $name => $path) {
            $files[$name] = $this->listFiles($path, $placeholders);
        }

        return ['database' => $snapshot, 'files' => $files];
    }

    public static function encode(mixed $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * A new link to an existing element takes the position after that
     * element's existing links, which depends on the database the test runs
     * on: it is kept as the distance from the last of them, `+<n>`.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function relativeLinkPosition(array $row): array
    {
        $lastPosition = (int)$this->db->table('structure_links')
            ->where('parentStructureId', '=', $row['parentStructureId'])
            ->where('type', '=', $row['type'])
            ->where('id', '<=', $this->lastIds['structure_links'])
            ->max('position');
        $row['position'] = '+' . ((int)$row['position'] - $lastPosition);
        return $row;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, string> $placeholders
     * @return array<string, mixed>
     */
    private function normaliseRow(array $row, array $placeholders, int $finishedAt): array
    {
        foreach ($row as $column => $value) {
            if ($value === null) {
                continue;
            }
            $text = (string)$value;
            if (isset($placeholders[$text])) {
                $row[$column] = $placeholders[$text];
            } elseif (preg_match('/date|time/i', $column) === 1 && $this->isWithinRun($text, $finishedAt)) {
                $row[$column] = '#now';
            } elseif (is_string($value)) {
                $row[$column] = preg_replace_callback(
                    '/(?<!\\d)\\d+(?!\\d)/',
                    static fn(array $match): string => $placeholders[$match[0]] ?? $match[0],
                    $value,
                );
            }
        }
        ksort($row);
        return $row;
    }

    private function isWithinRun(string $value, int $finishedAt): bool
    {
        $timestamp = ctype_digit($value) ? (int)$value : strtotime($value);
        return $timestamp !== false && $timestamp >= $this->startedAt && $timestamp <= $finishedAt;
    }

    /**
     * @param array<string, string> $placeholders
     * @return array<string, string>
     */
    private function listFiles(string $path, array $placeholders): array
    {
        if (!is_dir($path)) {
            return [];
        }
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS));
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $relativePath = substr($file->getPathname(), strlen($path));
            $name = $placeholders[$relativePath] ?? $relativePath;
            $files[$name] = $file->getSize() . ' ' . md5_file($file->getPathname());
        }
        ksort($files);
        return $files;
    }
}
