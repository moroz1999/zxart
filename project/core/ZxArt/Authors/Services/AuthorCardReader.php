<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use ZxArt\Authors\Dto\AuthorCardDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * @psalm-api
 */
final readonly class AuthorCardReader
{
    public function __construct(
        private AuthorRepository $authorRepository,
        private AuthorFieldsReader $authorFieldsReader,
        private MembershipsReader $membershipsReader,
        private AuthorDetailsService $authorDetailsService,
        private EntityRefsReader $entityRefsReader,
    ) {
    }

    /**
     * @throws AuthorDataException for an id of no author
     */
    public function read(int $authorId): AuthorCardDto
    {
        $author = $this->authorRepository->get($authorId);
        $fields = $this->authorFieldsReader->read($author);

        return new AuthorCardDto(
            id: $authorId,
            fields: $fields,
            refs: $this->entityRefsReader->read([$fields->countryId ?? 0, $fields->cityId ?? 0]),
            aliases: $this->membershipsReader->aliases((array)$author->getAliasElements()),
            memberships: $this->membershipsReader->groupsOf($authorId),
            counters: $this->authorDetailsService->getCounters($author),
        );
    }
}
