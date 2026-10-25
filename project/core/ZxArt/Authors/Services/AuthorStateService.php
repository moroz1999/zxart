<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\AuthorStateDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Import\Services\ImportOriginsReader;
use ZxArt\Shared\EntityType;

/**
 * Reads the complete state of an author. Checks no privilege beyond what
 * loading the element checks.
 *
 * @psalm-api
 */
final readonly class AuthorStateService
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorFieldsReader $authorFieldsReader,
        private AuthorshipDbal $authorshipDbal,
        private AuthorDetailsService $authorDetailsService,
        private ImportOriginsReader $importOriginsReader,
    ) {
    }

    public function get(int $authorId): AuthorStateDto
    {
        $author = $this->structureManager->getElementById($authorId);
        if (!$author instanceof authorElement) {
            throw new AuthorDataException('Author not found', 404);
        }

        return new AuthorStateDto(
            id: $authorId,
            modifiedAt: $author->getModifiedTimestamp(),
            fields: $this->authorFieldsReader->read($author),
            aliasIds: array_values(array_map('intval', $author->getAliasElementsIds())),
            memberships: $this->authorshipDbal->findByAuthor($authorId, EntityType::Group),
            counters: $this->authorDetailsService->getCounters($author),
            importOrigins: $this->importOriginsReader->read($authorId),
        );
    }
}
