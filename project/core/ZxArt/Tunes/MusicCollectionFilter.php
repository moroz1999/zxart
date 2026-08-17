<?php

declare(strict_types=1);

namespace ZxArt\Tunes;

/**
 * Named subsets of the music collection offered by the top-music page. The case
 * value is the URL segment the SPA route carries, so a tag element id never
 * becomes part of a public URL.
 *
 * Sound-type filters name the format groups a tune can be written for
 * (`MusicSettingsProvider::getFormatGroups()`); the remaining ones narrow by
 * tag or by the presence of a linked game.
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
            self::Cover, self::Original, self::Games => [],
        };
    }

    /** Whether the filter keeps only tunes written for a game. */
    public function gameOnly(): bool
    {
        return $this === self::Games;
    }
}
