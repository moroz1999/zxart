<?php

declare(strict_types=1);

namespace ZxArt\Tunes;

use ZxArt\ZxProdCategories\CategoryIds;

/**
 * Named subsets of the music collection offered by the top-music page. The case
 * value is the URL segment the SPA route carries, so a tag element id never
 * becomes part of a public URL.
 *
 * Sound-type filters name the format groups a tune can be written for
 * (`MusicSettingsProvider::getFormatGroups()`); the remaining ones narrow by
 * tag or by the category of the production the tune belongs to.
 */
enum MusicCollectionFilter: string
{
    private const int COVER_TAG_ID = 81401;

    case Cover = 'cover';
    case Original = 'original';
    case Ay = 'ay';
    case Beeper = 'beeper';
    case DigitalAy = 'digitalay';
    case SamCoupe = 'samcoupe';
    case TurboSound = 'turbosound';
    case Fm = 'fm';
    case Games = 'games';
    case Demos = 'demos';
    case Press = 'press';

    /** Tag element the tunes must carry, or null when the filter is not tag-based. */
    public function tagId(): ?int
    {
        return $this === self::Cover ? self::COVER_TAG_ID : null;
    }

    /** Tag element the tunes must not carry, or null when nothing is excluded. */
    public function excludedTagId(): ?int
    {
        return $this === self::Original ? self::COVER_TAG_ID : null;
    }

    /**
     * Format groups the filter accepts, empty when it does not filter by sound.
     *
     * @return string[]
     */
    public function formatGroups(): array
    {
        return match ($this) {
            self::Ay => ['ay', 'aybeeper', 'aydigitalay', 'aycovox'],
            self::Beeper => ['beeper', 'digitalbeeper', 'beeperdigitalbeeper'],
            self::DigitalAy => ['digitalay', 'aydigitalay'],
            self::SamCoupe => ['saa'],
            self::TurboSound => ['ts', 'tsfm'],
            self::Fm => ['fm', 'tsfm'],
            self::Cover, self::Original, self::Games, self::Demos, self::Press => [],
        };
    }

    /**
     * Category the tune's production must belong to, or null when the filter
     * does not look at the production. The whole subtree of the category counts.
     */
    public function prodCategory(): ?CategoryIds
    {
        return match ($this) {
            self::Games => CategoryIds::GAMES,
            self::Demos => CategoryIds::DEMOSCENE,
            self::Press => CategoryIds::PRESS,
            default => null,
        };
    }
}
