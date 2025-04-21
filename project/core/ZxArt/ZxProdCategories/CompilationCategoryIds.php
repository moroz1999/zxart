<?php
declare(strict_types=1);


namespace ZxArt\ZxProdCategories;

final class CompilationCategoryIds
{
    private const CATEGORY_IDS = [
        CategoryIds::EDUCATIONAL->value => CategoryIds::COMPILATION_EDUCATIONAL->value,
        CategoryIds::GAMES->value => CategoryIds::COMPILATION_GAMES->value,
        CategoryIds::DEMOS->value => CategoryIds::COMPILATION_DEMOS->value,
        CategoryIds::PRESS->value => CategoryIds::COMPILATION_MAGAZINES->value,
        CategoryIds::SYSTEM_SOFTWARE->value => CategoryIds::COMPILATION_UTILITIES->value,
    ];

    public static function getCompilationCategoryId(int $categoryId): ?int
    {
        return self::CATEGORY_IDS[$categoryId] ?? null;
    }
}