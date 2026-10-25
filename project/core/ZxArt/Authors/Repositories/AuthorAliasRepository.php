<?php

declare(strict_types=1);

namespace ZxArt\Authors\Repositories;

use authorAliasElement;
use structureManager;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Services\AuthorsService;

/**
 * Supplies author alias to the use cases and stores them: every write of
 * an author alias's own data goes through `save()`.
 *
 * @psalm-api
 */
final readonly class AuthorAliasRepository
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorsService $authorsService,
    ) {
    }

    /**
     * @throws AuthorAliasDataException 400 without an id, 404 for no author alias of it
     */
    public function get(int $aliasId): authorAliasElement
    {
        if ($aliasId <= 0) {
            throw new AuthorAliasDataException('Missing required author alias id', 400);
        }
        $alias = $this->structureManager->getElementById($aliasId);
        if (!$alias instanceof authorAliasElement) {
            throw new AuthorAliasDataException('Author alias not found', 404);
        }

        return $alias;
    }

    /**
     * A new author alias in the catalogue folder of its name's first letter, its
     * structure record not yet stored.
     *
     * @throws AuthorAliasDataException 500 when the element cannot be made
     */
    public function create(string $name): authorAliasElement
    {
        $alias = $this->authorsService->manufactureAliasElement($name);
        if ($alias === null) {
            throw new AuthorAliasDataException('The element could not be created', 500);
        }

        return $alias;
    }

    public function save(authorAliasElement $alias): void
    {
        $alias->persistElementData();
    }

    /**
     * Moves the modification time after a change kept outside the element.
     */
    public function touch(authorAliasElement $alias): void
    {
        $alias->persistModificationTime();
    }

    public function delete(authorAliasElement $alias): void
    {
        $alias->deleteElementData();
    }
}
