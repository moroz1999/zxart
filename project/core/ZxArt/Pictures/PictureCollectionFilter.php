<?php

declare(strict_types=1);

namespace ZxArt\Pictures;

/**
 * Named subsets of the picture collection offered by the top-graphics page.
 * The case value is the URL segment the SPA route carries, so a tag element id
 * never becomes part of a public URL.
 *
 * A filter narrows either by tag or by picture format, never by both.
 */
enum PictureCollectionFilter: string
{
    case LoadingScreens = 'loading';
    case GameGraphics = 'ingame';
    case NoCopy = 'nocopy';
    case Gigascreen = 'gigascreen';
    case SamCoupe = 'samcoupe';
    case SpectrumNext = 'next';

    /** Tag element the filter narrows to, or null when it narrows by format. */
    public function tagId(): ?int
    {
        return match ($this) {
            self::LoadingScreens => 46245,
            self::GameGraphics => 47883,
            self::NoCopy => 48471,
            self::Gigascreen, self::SamCoupe, self::SpectrumNext => null,
        };
    }

    /**
     * Picture formats the filter narrows to, empty when it narrows by tag.
     *
     * @return string[]
     */
    public function formats(): array
    {
        return match ($this) {
            // every format built on gigascreen frame mixing, including the
            // multigigascreen and lowres variants
            self::Gigascreen => ['gigascreen', 'lowresgs', 'mg1', 'mg2', 'mg4', 'mg8', 'timexhrg', 'stellar'],
            self::SamCoupe => ['sam2', 'sam3', 'sam4'],
            self::SpectrumNext => ['nxi', 'sl2'],
            self::LoadingScreens, self::GameGraphics, self::NoCopy => [],
        };
    }
}
