<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

final readonly class AuthorAliasYearNormalizer
{
    private const string STORAGE_DATE_PREFIX = '01.01.';

    public function toStorageDate(string $year): string
    {
        $year = trim($year);
        if (preg_match('/^\d{4}$/D', $year) !== 1) {
            return $year;
        }

        return self::STORAGE_DATE_PREFIX . $year;
    }

    public function toFormYear(string $date): string
    {
        if (preg_match('/^\d{2}\.\d{2}\.(\d{4})$/D', $date, $matches) !== 1) {
            return $date;
        }

        return $matches[1];
    }
}
