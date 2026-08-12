<?php

declare(strict_types=1);

namespace ZxArt\Social;

use ZxArtItem;

readonly class PostTitleBuilder
{
    private const string AUTHORS_SEPARATOR = ' / ';

    public function build(ZxArtItem $element): string
    {
        $title = html_entity_decode((string)$element->getTitle(), ENT_QUOTES);
        $authors = html_entity_decode($element->getAuthorNamesString(), ENT_QUOTES);
        if ($authors === '') {
            return $title;
        }

        return $title . self::AUTHORS_SEPARATOR . $authors;
    }
}
