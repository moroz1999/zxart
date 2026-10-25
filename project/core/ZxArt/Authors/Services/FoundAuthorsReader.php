<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use ZxArt\Authors\Dto\FoundAuthorDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityRefs;

/**
 * The summaries of the authors a search found, in the order of the ids.
 *
 * @psalm-api
 */
final readonly class FoundAuthorsReader
{
    public function __construct(
        private AuthorRepository $authorRepository,
    ) {
    }

    /**
     * @param int[] $authorIds
     * @return list<FoundAuthorDto>
     * @throws AuthorDataException for an id of no author
     */
    public function read(array $authorIds): array
    {
        $found = [];
        foreach ($authorIds as $authorId) {
            $author = $this->authorRepository->get($authorId);
            $country = $author->getCountryElement();
            $city = $author->getCityElement();
            $found[] = new FoundAuthorDto(
                id: $authorId,
                title: html_entity_decode(ElementFields::text($author, 'title'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                realName: html_entity_decode(ElementFields::text($author, 'realName'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                country: $country !== null ? EntityRefs::of($country) : null,
                city: $city !== null ? EntityRefs::of($city) : null,
                aliases: EntityRefs::list((array)$author->getAliasElements()),
                groups: EntityRefs::list((array)$author->getGroupsList()),
            );
        }

        return $found;
    }
}
