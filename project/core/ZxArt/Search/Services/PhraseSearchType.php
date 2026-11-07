<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use ZxArt\Shared\DatabaseTable;

/**
 * A kind of entity the phrase search looks through, by the structure type it
 * is stored as: where it lies, which columns are its titles and which its texts.
 */
enum PhraseSearchType: string
{
    case Author = 'author';
    case AuthorAlias = 'authorAlias';
    case Group = 'group';
    case GroupAlias = 'groupAlias';
    case ZxProd = 'zxProd';
    case ZxRelease = 'zxRelease';
    case ZxPicture = 'zxPicture';
    case ZxMusic = 'zxMusic';
    case Party = 'party';
    case PressArticle = 'pressArticle';
    case Country = 'country';
    case City = 'city';

    public function table(): DatabaseTable
    {
        return match ($this) {
            self::Author => DatabaseTable::Author,
            self::AuthorAlias => DatabaseTable::AuthorAlias,
            self::Group => DatabaseTable::Group,
            self::GroupAlias => DatabaseTable::GroupAlias,
            self::ZxProd => DatabaseTable::ZxProd,
            self::ZxRelease => DatabaseTable::ZxRelease,
            self::ZxPicture => DatabaseTable::ZxPicture,
            self::ZxMusic => DatabaseTable::ZxMusic,
            self::Party => DatabaseTable::Party,
            self::PressArticle => DatabaseTable::PressArticle,
            self::Country => DatabaseTable::Country,
            self::City => DatabaseTable::City,
        };
    }

    /**
     * The columns a title is matched in, the main title first: results are
     * ordered by it.
     *
     * @return non-empty-list<string>
     */
    public function titleColumns(): array
    {
        return match ($this) {
            self::Author => ['title', 'realName'],
            self::ZxProd => ['title', 'altTitle'],
            self::ZxMusic => ['title', 'internalTitle'],
            self::PressArticle => ['title', 'introduction'],
            default => ['title'],
        };
    }

    /**
     * The texts the full search matches as well.
     *
     * @return list<string>
     */
    public function contentColumns(): array
    {
        return match ($this) {
            self::ZxProd, self::ZxRelease, self::ZxPicture, self::ZxMusic => ['description'],
            self::PressArticle => ['content'],
            default => [],
        };
    }

    /**
     * The owner an alias is answered under when aliases merge into their owners.
     */
    public function aliasOwner(): ?self
    {
        return match ($this) {
            self::AuthorAlias => self::Author,
            self::GroupAlias => self::Group,
            default => null,
        };
    }
}
