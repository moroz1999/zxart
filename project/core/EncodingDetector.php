<?php

class EncodingDetector
{
    private static $letters = [
        'о', 'е', 'а', 'и', 'н', 'т', 'с', 'р', 'в', 'л', 'к', 'м', 'д', 'п', 'у', 'я', 'ы', 'ь', 'г', 'з',
        'б', 'ч', 'й', 'х', 'ж', 'ш', 'ю', 'ц', 'щ', 'э', 'ф', 'ъ', 'ё',
    ];

    private static array $encodings = [
        'Windows-1251',
        'CP866',
        'KOI8-R',
        'ISO-8859-1',
        'Windows-1252',
    ];

    public static function decodeText(string $content): string|false
    {
        $encoding = self::detectEncoding($content);
        if ($encoding) {
            return mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        return false;
    }

    public static function detectEncoding(string $content): ?string
    {
        $autoEncoding = mb_detect_encoding($content, implode(', ', self::$encodings), true);
        if ($autoEncoding === 'UTF-8') {
            if (self::isMostlyPrintable($content)) {
                return $autoEncoding;
            }
        }

        $content = self::sanitizeText($content);
        $textWeights = [];
        foreach (self::$encodings as $encoding) {
            $textWeights[$encoding] = self::countWeight($content, $encoding);
        }
        $result = array_search(max($textWeights), $textWeights, true);
        if ($result !== false) {
            return $result;
        }

        return null;
    }

    public static function isMostlyPrintable(string $str): bool
    {
        $length = min(mb_strlen($str), 300);
        if (!$length) {
            return false;
        }
        $nonPrintable = 0;

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($str, $i, 1);
            if (!preg_match("/[[:print:]\p{Cyrillic}\s\n\r]/u", $char)) {
                $nonPrintable++;
            }
        }

        return ($nonPrintable / $length) < 0.1;
    }

    private static function countWeight(string $text, string $encoding): int
    {
        $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        $letters = array_count_values(mb_str_split($text));
        arsort($letters);
        return count(array_intersect(self::$letters, array_keys($letters)));
    }

    private static function sanitizeText(string $text): string
    {
        return str_replace(["\n", "\r\n", "\r", " "], '', $text);
    }
}