<?php

declare(strict_types=1);

namespace ZxArt\Releases;

use ZxArt\Releases\Dto\ReleaseDto;
use ZxArt\Shared\Dto\AuthorDto;
use zxReleaseElement;

readonly class ReleasesTransformer
{
    public function toDto(zxReleaseElement $element): ReleaseDto
    {
        $authors = [];
        foreach ($element->getAuthorsList() as $author) {
            $authors[] = new AuthorDto(
                name: html_entity_decode((string)$author->getTitle(), ENT_QUOTES),
                url: $author->getUrl(),
            );
        }

        return new ReleaseDto(
            id: (int)$element->id,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            url: $element->getUrl(),
            year: $element->year ? (string)$element->year : null,
            votes: (float)$element->votes,
            authors: $authors,
        );
    }
}
