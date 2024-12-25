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
        return !empty($value1) && !empty($value2) && $value1 === $value2;
    }

    public function alphanumericValueMatches(?string $value1, ?string $value2): bool
    {
        return !empty($value1) && !empty($value2) && $this->alphanumericColumnSearch->toAlphanumeric($value1) === $this->alphanumericColumnSearch->toAlphanumeric($value2);
    }

    public function valueStartMatches(?string $value1, ?string $value2): bool
    {
        return !empty($value1) && !empty($value2) && mb_stripos($value1, $value2) === 0;
    }
}