<?php

declare(strict_types=1);

namespace ZxArt\Tags;

enum TagSection: string
{
    case Graphics = 'graphics';
    case Music = 'music';
    case Software = 'software';

    public static function fromRoutePrefix(string $prefix): ?self
    {
        return match ($prefix) {
            'pictures' => self::Graphics,
            'music' => self::Music,
            'prods' => self::Software,
            default => null,
        };
    }

    public function routePrefix(): string
    {
        return match ($this) {
            self::Graphics => '/pictures',
            self::Music => '/music',
            self::Software => '/prods',
        };
    }

    public function tagPath(int $tagId): string
    {
        return $this->routePrefix() . '/tags/' . $tagId;
    }
}
