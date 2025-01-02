<?php
declare(strict_types=1);


namespace ZxArt\Import;

use ZxArt\Helpers\AlphanumericColumnSearch;

final readonly class Resolver
{
    public function __construct(
        private AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {
    }

    public function intMatches(?int $value1, ?int $value2): bool
    {
        return $value1 !== null && $value2 !== null && $value1 > 0 && $value2 > 0 && $value1 === $value2;
    }

    public function valueMatches(?string $value1, ?string $value2): bool
    {
        $value1 = trim($value1 ?? '');
        $value2 = trim($value2 ?? '');
        return !empty($value1) && !empty($value2) && mb_strtolower($value1) === mb_strtolower($value2);
    }

    public function alphanumericValueMatches(?string $value1, ?string $value2): bool
    {
        $value1 = trim($value1 ?? '');
        $value2 = trim($value2 ?? '');
        return !empty($value1) && !empty($value2) && $this->alphanumericColumnSearch->toAlphanumeric($value1) === $this->alphanumericColumnSearch->toAlphanumeric($value2);
    }

    public function valueStartsWith(?string $haystack, ?string $needle): bool
    {
        $haystack = trim($haystack ?? '');
        $needle = trim($needle ?? '');
        return !empty($haystack) && !empty($needle) && mb_stripos($haystack, $needle) === 0;
    }
}