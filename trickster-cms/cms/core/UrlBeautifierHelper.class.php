<?php

class UrlBeautifierHelper
{
    public static function convert($input)
    {
        $input = html_entity_decode(trim($input), ENT_QUOTES);
        $input = TranslitHelper::convert($input);
        $input = str_replace('+', ' ', $input);
        $input = trim($input);
        $input = preg_replace('/\s+/', '-', $input);
        $input = strtolower($input);
        $input = preg_replace("/[^0-9a-z-_]/", '', $input);
        return $input;
    }
}