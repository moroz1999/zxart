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
    case PressArticle = 'pressArticle';

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

    /**
     * The element action that receives the submitted values. A press article is
     * created and edited by the same action, so its privileges are held under
     * that single name.
     */
    public function getSubmitAction(): string
    {
        return match ($this) {
            self::ProdBatch, self::PictureBatch, self::MusicBatch => 'batchUpload',
            self::PressArticle => 'publicReceive',
            default => 'publicAdd',
        };
    }
}
