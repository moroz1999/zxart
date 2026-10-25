<?php

declare(strict_types=1);

namespace ZxArt\Import\Prods;

/**
 * Turns a catalogue title with its article moved to the end, as in
 * "Hobbit, The", back into "The Hobbit".
 */
final readonly class ImportedTitleSanitizer
{
    private const array ARTICLES = ['The', 'La', 'El', 'A'];

    public function sanitize(string $title): string
    {
        foreach (self::ARTICLES as $article) {
            $search = ', ' . $article;
            if (mb_stripos($title, $search) !== false) {
                $title = $article . ' ' . mb_substr($title, 0, mb_strlen($search) * (-1));
            }
            $search = ',' . $article;
            if (mb_stripos($title, $search) !== false) {
                $title = $article . ' ' . mb_substr($title, 0, mb_strlen($search) * (-1));
            }
        }

        return $title;
    }
}
