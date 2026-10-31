<?php

declare(strict_types=1);

namespace ZxArt\ZxScreen;

use ZxImage\Converter;

/**
 * Renders a ZX Spectrum screen file to an image the way the site shows it: a
 * PNG, or an animated GIF for a flickering screen; the colours mixed, sRGB
 * palette, zoomed twice, no border.
 *
 * @psalm-api
 */
final readonly class ZxScreenRenderer
{
    private const int ZOOM = 2;

    /**
     * @param string $type a converter type, as {@see \fileElement::getZxScreenType()} names it
     * @return string|null the image's bytes, null if the file cannot be rendered
     */
    public function render(string $path, string $type): ?string
    {
        return (new Converter())
            ->setType($type)
            ->setPath($path)
            ->setGigascreenMode('mix')
            ->setPalette('srgb')
            ->setZoom(self::ZOOM)
            ->getBinary();
    }
}
