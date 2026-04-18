<?php

declare(strict_types=1);

namespace ZxArt\Shared;

final class LatinCyrillicMap
{
    private const array MAP = [
        'A' => 'А', 'B' => 'Б', 'C' => 'Ц', 'D' => 'Д', 'E' => 'Е',
        'F' => 'Ф', 'G' => 'Г', 'H' => 'Х', 'I' => 'И', 'J' => 'Й',
        'K' => 'К', 'L' => 'Л', 'M' => 'М', 'N' => 'Н', 'O' => 'О',
        'P' => 'П', 'R' => 'Р', 'S' => 'С', 'T' => 'Т', 'U' => 'У',
        'V' => 'В', 'W' => 'В', 'X' => 'Х', 'Y' => 'У', 'Z' => 'З',
    ];

    public static function getCyrillicForLatin(string $latin): ?string
    {
        return self::MAP[$latin] ?? null;
    }

    public static function getLatinForCyrillic(string $cyrillic): ?string
    {
        $result = array_search($cyrillic, self::MAP, true);

        return $result !== false ? $result : null;
    }

    /**
     * @return string[]
     */
    public static function getEquivalentLetters(string $letter): array
    {
        $upper = mb_strtoupper($letter);
        $letters = [$upper];

        $cyrillic = self::getCyrillicForLatin($upper);
        if ($cyrillic !== null) {
            $letters[] = $cyrillic;
        } else {
            $latin = self::getLatinForCyrillic($upper);
            if ($latin !== null) {
                $letters[] = $latin;
            }
        }

        return $letters;
    }
}
