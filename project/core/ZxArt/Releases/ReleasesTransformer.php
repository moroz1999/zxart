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
                name: (string)$author->getTitle(),
                url: $author->getUrl(),
            );
        }

        return new ReleaseDto(
            id: (int)$element->id,
            title: (string)$element->getTitle(),
            url: $element->getUrl(),
            year: $element->year ? (string)$element->year : null,
            votes: (float)$element->votes,
            authors: $authors,
        );
    }
}
