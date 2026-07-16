<?php

declare(strict_types=1);

namespace ZxArt\Releases;

use ZxArt\Releases\Dto\ReleaseDto;
use ZxArt\Shared\Dto\AuthorDto;
use zxReleaseElement;

readonly class ReleasesTransformer
{
    public function __construct(
        private \ZxArt\Urls\EntityUrlResolver $entityUrlResolver,
    ) {
    }

    public function toDto(zxReleaseElement $element): ReleaseDto
    {
        $authors = [];
        foreach ($element->getAuthorsList() as $author) {
            $authors[] = new AuthorDto(
                name: html_entity_decode((string)$author->getTitle(), ENT_QUOTES),
                url: $this->entityUrlResolver->urlFor($author),
            );
        }

        return new ReleaseDto(
            id: (int)$element->id,
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            url: $this->entityUrlResolver->urlFor($element),
            year: $element->year ? (string)$element->year : null,
            votes: (float)$element->votes,
            authors: $authors,
        );
    }
}
