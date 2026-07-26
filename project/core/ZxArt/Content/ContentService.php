<?php

declare(strict_types=1);

namespace ZxArt\Content;

/**
 * Serves static, non-editable content pages (About, FAQ, support, API) from
 * bundled HTML files. Pages are whitelisted; missing translations fall back to
 * English.
 */
readonly class ContentService
{
    private const array PAGES = ['about', 'faq', 'support', 'api'];
    private const array LANGUAGES = ['en', 'ru', 'es'];
    private const string FALLBACK_LANGUAGE = 'en';

    /** Returns the page HTML for the language (English fallback), or null if the page is unknown/missing. */
    public function getContent(string $page, string $language): ?string
    {
        if (!in_array($page, self::PAGES, true)) {
            return null;
        }
        if (!in_array($language, self::LANGUAGES, true)) {
            $language = self::FALLBACK_LANGUAGE;
        }

        $html = $this->readFile($page, $language);
        if ($html === null && $language !== self::FALLBACK_LANGUAGE) {
            $html = $this->readFile($page, self::FALLBACK_LANGUAGE);
        }

        return $html;
    }

    private function readFile(string $page, string $language): ?string
    {
        $path = __DIR__ . '/pages/' . $page . '.' . $language . '.html';
        if (!is_file($path)) {
            return null;
        }
        $content = file_get_contents($path);

        return $content === false ? null : $content;
    }
}
