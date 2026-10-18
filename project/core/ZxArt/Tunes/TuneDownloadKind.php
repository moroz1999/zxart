<?php

declare(strict_types=1);

namespace ZxArt\Tunes;

/**
 * What a downloadable file of a tune is; the interface labels each kind.
 */
enum TuneDownloadKind: string
{
    /** The file the tune is played and converted from. */
    case Original = 'original';
    /** The tracker's own source module. */
    case Tracker = 'tracker';
    /** A runnable program that plays the tune. */
    case Executable = 'executable';
    /** The recorded audio. */
    case Audio = 'audio';
}
