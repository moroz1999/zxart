<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorCommand;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorHandler;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\DeleteAuthorAliasCommand;
use ZxArt\Authors\UseCases\DeleteAuthorAliasHandler;
use ZxArt\Authors\UseCases\JoinAuthorAliasesCommand;
use ZxArt\Authors\UseCases\JoinAuthorAliasesHandler;
use ZxArt\Authors\UseCases\UpdateAuthorAliasCommand;
use ZxArt\Authors\UseCases\UpdateAuthorAliasHandler;

/**
 * Changes to an author alias requested through its data endpoint, each one a use case
 * in `ZxArt\Authors\UseCases`: it checks the privilege of the matching legacy action, runs in
 * one transaction and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class AuthorAliasDataService
{
    public function __construct(
        private CreateAuthorAliasHandler $createAuthorAliasHandler,
        private UpdateAuthorAliasHandler $updateAuthorAliasHandler,
        private DeleteAuthorAliasHandler $deleteAuthorAliasHandler,
        private ConvertAuthorAliasToAuthorHandler $convertAuthorAliasToAuthorHandler,
        private JoinAuthorAliasesHandler $joinAuthorAliasesHandler,
    ) {
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function create(AuthorAliasFieldsDto $fields): AuthorAliasDataResultDto
    {
        return $this->createAuthorAliasHandler->handle(new CreateAuthorAliasCommand($fields));
    }

    /**
     * Changes the fields the DTO carries and leaves the others as they are.
     *
     * @throws AuthorAliasDataException
     */
    public function update(int $aliasId, AuthorAliasFieldsDto $fields): AuthorAliasDataResultDto
    {
        return $this->updateAuthorAliasHandler->handle(new UpdateAuthorAliasCommand($aliasId, $fields));
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function delete(int $aliasId): AuthorAliasDataResultDto
    {
        return $this->deleteAuthorAliasHandler->handle(new DeleteAuthorAliasCommand($aliasId));
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function convertToAuthor(int $aliasId): AuthorAliasDataResultDto
    {
        return $this->convertAuthorAliasToAuthorHandler->handle(new ConvertAuthorAliasToAuthorCommand($aliasId));
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function join(int $aliasId, int $joinedId): AuthorAliasDataResultDto
    {
        return $this->joinAuthorAliasesHandler->handle(new JoinAuthorAliasesCommand($aliasId, $joinedId));
    }
}
