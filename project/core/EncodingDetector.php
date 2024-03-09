<?php

class EncodingDetector
{
    static array $encodings = ['UTF-8', 'Windows-1251', 'ISO-8859-1', 'IBM866',  'CP866', 'KOI8-R', 'Windows-1252'];

    /**
     * @param string $content
     * @return string|false
     */
    public static function decodeText(string $content): string|false
    {
        $encoding = self::detectEncoding($content);
        if ($encoding) {
            return mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        return false;
    }

    public static function detectEncoding(string $content)
    {
        $autoEncoding = mb_detect_encoding($content, implode(', ', self::$encodings), true);
        if ($autoEncoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $autoEncoding);
        }
        if (self::isMostlyPrintable($content)) {
            return $autoEncoding;
        }

        foreach (self::$encodings as $testEncoding) {
            $content = mb_convert_encoding($content, 'UTF-8', $testEncoding);

            if (self::isMostlyPrintable($content)) {
                return $testEncoding;
            }
        }

        return false;
    }

    public static function isMostlyPrintable(string $str): bool
    {
        $length = min(mb_strlen($str), 100);
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

}