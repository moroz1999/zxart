<?php

declare(strict_types=1);

namespace ZxArt\Forms;

enum FormCreateType: string
{
    case Author = 'author';
    case Group = 'group';
    case Party = 'party';
    case ProdBatch = 'prodBatch';
    case PictureBatch = 'pictureBatch';
    case MusicBatch = 'musicBatch';
    case Release = 'release';

    /**
     * Batch upload forms create several works at once from the uploaded files;
     * they use their own element actions and are attached to the element the
     * upload was started from.
     */
    public function isBatchUpload(): bool
    {
        return match ($this) {
            self::ProdBatch, self::PictureBatch, self::MusicBatch => true,
            default => false,
        };
    }
}
