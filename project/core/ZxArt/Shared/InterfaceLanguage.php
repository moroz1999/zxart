<?php

declare(strict_types=1);

namespace ZxArt\Shared;

/**
 * The site's interface languages, by ISO 639-1 code.
 *
 * This is the representation the outside world uses: the SPA translation files
 * (`en.json`, `ru.json`, `es.json`) and every REST payload that carries a
 * language. The CMS itself works in ISO 639-3 (`eng`, `rus`, `spa`) and
 * addresses languages by structure element id, so
 * {@see self::fromIso6393()} bridges the two.
 *
 * Distinct from the software languages a production can be written in
 * (`zxitem_language`) — those describe content, these describe the interface.
 */
enum InterfaceLanguage: string
{
    case En = 'en';
    case Ru = 'ru';
    case Es = 'es';

    public static function fromIso6393(string $code): ?self
    {
        return match (strtolower($code)) {
            'eng' => self::En,
            'rus' => self::Ru,
            'spa' => self::Es,
            default => null,
        };
    }

    public function toIso6393(): string
    {
        return match ($this) {
            self::En => 'eng',
            self::Ru => 'rus',
            self::Es => 'spa',
        };
    }
}
