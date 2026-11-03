<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorDetailsService;
use ZxArt\Authors\Services\MembershipsReader;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * Builds the assistant's author and author alias DTOs from the stored elements.
 * Checks no privilege.
 *
 * @psalm-api
 */
final readonly class AuthorDtos
{
    public function __construct(
        private AuthorRepository $authorRepository,
        private AuthorAliasRepository $authorAliasRepository,
        private MembershipsReader $membershipsReader,
        private AuthorDetailsService $authorDetailsService,
        private EntityRefsReader $entityRefsReader,
    ) {
    }

    /**
     * @throws AuthorDataException for an id of no author
     */
    public function author(int $authorId): AuthorDto
    {
        $author = $this->authorRepository->get($authorId);
        $countryId = ElementFields::number($author, 'country');
        $cityId = ElementFields::number($author, 'city');

        return new AuthorDto(
            id: $authorId,
            title: ElementFields::text($author, 'title'),
            realName: ElementFields::text($author, 'realName'),
            countryId: $countryId,
            cityId: $cityId,
            wikiLink: ElementFields::text($author, 'wikiLink'),
            refs: $this->entityRefsReader->read([$countryId, $cityId]),
            aliases: $this->membershipsReader->aliases($author->getAliasElements()),
            groups: $this->membershipsReader->groupsOf($authorId),
            counters: $this->authorDetailsService->getCounters($author),
        );
    }

    /**
     * @throws AuthorAliasDataException for an id of no author alias
     */
    public function alias(int $aliasId): AuthorAliasDto
    {
        $alias = $this->authorAliasRepository->get($aliasId);
        $authorId = ElementFields::number($alias, 'authorId');

        return new AuthorAliasDto(
            id: $aliasId,
            title: ElementFields::text($alias, 'title'),
            authorId: $authorId,
            startDate: ElementFields::timestamp($alias, 'startDate'),
            endDate: ElementFields::timestamp($alias, 'endDate'),
            refs: $this->entityRefsReader->read([$authorId]),
            groups: $this->membershipsReader->groupsOf($aliasId),
            counters: $this->authorDetailsService->getCounters($alias),
        );
    }
}
