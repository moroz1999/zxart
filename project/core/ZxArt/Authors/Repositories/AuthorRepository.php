<?php

declare(strict_types=1);

namespace ZxArt\Authors\Repositories;

use authorElement;
use structureManager;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Services\AuthorsService;

/**
 * Supplies authors to the use cases and stores them: every write of
 * an author's own data goes through `save()`.
 *
 * @psalm-api
 */
final readonly class AuthorRepository
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorsService $authorsService,
    ) {
    }

    /**
     * @throws AuthorDataException 400 without an id, 404 for no author of it
     */
    public function get(int $authorId): authorElement
    {
        if ($authorId <= 0) {
            throw new AuthorDataException('Missing required author id', 400);
        }
        $author = $this->structureManager->getElementById($authorId);
        if (!$author instanceof authorElement) {
            throw new AuthorDataException('Author not found', 404);
        }

        return $author;
    }

    /**
     * A new author in the catalogue folder of its name's first letter, its
     * structure record not yet stored.
     *
     * @throws AuthorDataException 500 when the element cannot be made
     */
    public function create(string $name): authorElement
    {
        $author = $this->authorsService->manufactureAuthorElement($name);
        if ($author === null) {
            throw new AuthorDataException('The element could not be created', 500);
        }

        return $author;
    }

    public function save(authorElement $author): void
    {
        $author->persistElementData();
    }

    /**
     * Moves the modification time after a change kept outside the element.
     */
    public function touch(authorElement $author): void
    {
        $author->persistModificationTime();
    }

    public function delete(authorElement $author): void
    {
        $author->deleteElementData();
    }
}
