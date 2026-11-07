<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use partyElement;
use pressArticleElement;
use structureElement;
use ZxArtItem;
use zxProdElement;
use zxReleaseElement;

/**
 * The title a found element is offered under where the results of a phrase
 * are listed in short: decoded, with what tells it apart from the elements of
 * the same title where its type has that (an author's real name and place, an
 * alias's owner, a work's year and authors).
 *
 * @psalm-api
 */
final readonly class FoundElementTitle
{
    public function of(structureElement $element): string
    {
        $title = match (true) {
            $element instanceof authorElement,
            $element instanceof authorAliasElement,
            $element instanceof groupElement,
            $element instanceof groupAliasElement,
            $element instanceof partyElement,
            $element instanceof pressArticleElement,
            $element instanceof zxProdElement,
            $element instanceof zxReleaseElement,
            $element instanceof ZxArtItem => $element->getSearchTitle(),
            default => (string)$element->getTitle(),
        };

        return html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    }
}
