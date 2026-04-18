<?php

declare(strict_types=1);

namespace ZxArt\Shared;

readonly class SortingParams
{
    /** Maps frontend sort keys to actual DB column names. */
    private const array COLUMN_ALIASES = [
        'date' => 'dateAdded',
    ];

    public function __construct(
        public string $column,
        public SortDirection $direction,
    ) {
    }

    /**
     * @param string[] $allowedColumns
     */
    public static function fromRequest(
        string $sorting,
        array $allowedColumns,
        string $defaultColumn = 'title',
        SortDirection $defaultDirection = SortDirection::ASC,
    ): self {
        $parts = explode(',', $sorting, 2);
        $column = trim($parts[0]);
        $directionRaw = isset($parts[1]) ? strtolower(trim($parts[1])) : '';

        if (!in_array($column, $allowedColumns, true)) {
            $column = $defaultColumn;
        }
        $column = self::COLUMN_ALIASES[$column] ?? $column;
        $direction = SortDirection::tryFrom($directionRaw) ?? $defaultDirection;

        return new self($column, $direction);
    }
}
