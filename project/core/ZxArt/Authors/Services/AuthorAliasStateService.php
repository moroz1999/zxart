<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\AuthorAliasStateDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Import\Services\ImportOriginsReader;
use ZxArt\Shared\EntityType;

/**
 * Reads the complete state of an author alias. Checks no privilege beyond
 * what loading the element checks.
 *
 * @psalm-api
 */
final readonly class AuthorAliasStateService
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorAliasFieldsReader $authorAliasFieldsReader,
        private AuthorshipDbal $authorshipDbal,
        private AuthorDetailsService $authorDetailsService,
        private ImportOriginsReader $importOriginsReader,
    ) {
    }

    public function get(int $aliasId): AuthorAliasStateDto
    {
        $alias = $this->structureManager->getElementById($aliasId);
        if (!$alias instanceof authorAliasElement) {
            throw new AuthorAliasDataException('Author alias not found', 404);
        }

        return new AuthorAliasStateDto(
            id: $aliasId,
            modifiedAt: $alias->getModifiedTimestamp(),
            fields: $this->authorAliasFieldsReader->read($alias),
            memberships: $this->authorshipDbal->findByAuthor($aliasId, EntityType::Group),
            counters: $this->authorDetailsService->getCounters($alias),
            importOrigins: $this->importOriginsReader->read($aliasId),
        );
    }
}
