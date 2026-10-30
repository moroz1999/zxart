<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use ZxArt\Authors\Dto\AuthorAliasCardDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * @psalm-api
 */
final readonly class AuthorAliasCardReader
{
    public function __construct(
        private AuthorAliasRepository $authorAliasRepository,
        private AuthorAliasFieldsReader $authorAliasFieldsReader,
        private MembershipsReader $membershipsReader,
        private AuthorDetailsService $authorDetailsService,
        private EntityRefsReader $entityRefsReader,
    ) {
    }

    /**
     * @throws AuthorAliasDataException for an id of no author alias
     */
    public function read(int $aliasId): AuthorAliasCardDto
    {
        $alias = $this->authorAliasRepository->get($aliasId);
        $fields = $this->authorAliasFieldsReader->read($alias);

        return new AuthorAliasCardDto(
            id: $aliasId,
            fields: $fields,
            refs: $this->entityRefsReader->read([$fields->authorId ?? 0]),
            memberships: $this->membershipsReader->groupsOf($aliasId),
            counters: $this->authorDetailsService->getCounters($alias),
        );
    }
}
