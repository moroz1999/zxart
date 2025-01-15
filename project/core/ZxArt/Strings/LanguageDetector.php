<?php
declare(strict_types=1);


namespace ZxArt\Strings;

class LanguageDetector
{
    public function detectLanguage(string $text): ?string
    {
        $text = strip_tags($text);

        $engPattern = '/[a-zA-Z]/';
        $rusPattern = '/[а-яА-ЯёЁ]/u';
        $spaPattern = '/[áéíóúüñÁÉÍÓÚÜÑ]/u';

        $engCount = preg_match_all($engPattern, $text);
        $rusCount = preg_match_all($rusPattern, $text);
        $spaCount = preg_match_all($spaPattern, $text);

        $totalCount = $engCount + $rusCount + $spaCount;

        if ($totalCount === 0) {
            return null;
        }

        $engRatio = $engCount / $totalCount;
        $rusRatio = $rusCount / $totalCount;
        $spaRatio = $spaCount / $totalCount;

        $threshold = 0.6;

        if ($engRatio >= $threshold) {
            return 'eng';
        }

        if ($rusRatio >= $threshold) {
            return 'rus';
        }

        if ($spaRatio >= $threshold) {
            return 'spa';
        }

        return null;
    }

}