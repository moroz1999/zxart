<?php

declare(strict_types=1);

namespace ZxArt\Shared;

readonly class SortingParams
{
    private const array DIRECTION_ALIASES = ['asc', 'desc'];

    /** Maps frontend sort keys to actual DB column names. */
    private const array COLUMN_ALIASES = [
        'date' => 'dateAdded',
    ];

    public function __construct(
        public string $column,
        public string $direction,
    ) {
    }

    /**
     * @param string[] $allowedColumns
     */
    public static function fromRequest(
        string $sorting,
        array $allowedColumns,
        string $defaultColumn = 'title',
        string $defaultDirection = 'asc',
    ): self {
        $parts = explode(',', $sorting, 2);
        $column = trim($parts[0]);
        $direction = isset($parts[1]) ? strtolower(trim($parts[1])) : $defaultDirection;

        if (!in_array($column, $allowedColumns, true)) {
            $column = $defaultColumn;
        }
        $column = self::COLUMN_ALIASES[$column] ?? $column;
        if (!in_array($direction, self::DIRECTION_ALIASES, true)) {
            $direction = $defaultDirection;
        }

        return new self($column, $direction);
    }
}
