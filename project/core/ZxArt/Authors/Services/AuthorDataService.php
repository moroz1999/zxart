<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\MembershipInputDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\UseCases\ConvertAuthorToGroupCommand;
use ZxArt\Authors\UseCases\ConvertAuthorToGroupHandler;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Authors\UseCases\DeleteAuthorCommand;
use ZxArt\Authors\UseCases\DeleteAuthorHandler;
use ZxArt\Authors\UseCases\JoinAuthorsCommand;
use ZxArt\Authors\UseCases\JoinAuthorsHandler;
use ZxArt\Authors\UseCases\UpdateAuthorCommand;
use ZxArt\Authors\UseCases\UpdateAuthorHandler;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * Changes to an author requested through its data endpoint, each one a use case
 * in `ZxArt\Authors\UseCases`: it checks the privilege of the matching legacy action, runs in
 * one transaction and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class AuthorDataService
{
    public function __construct(
        private CreateAuthorHandler $createAuthorHandler,
        private UpdateAuthorHandler $updateAuthorHandler,
        private DeleteAuthorHandler $deleteAuthorHandler,
        private ConvertAuthorToGroupHandler $convertAuthorToGroupHandler,
        private JoinAuthorsHandler $joinAuthorsHandler,
    ) {
    }

    /**
     * A list left `null` stores nothing.
     *
     * @param array<int, string>|null $realNames language id => the real name in it
     * @param list<MembershipInputDto>|null $groups
     * @param list<MembershipInputDto>|null $prods
     * @param list<ImportOriginDto>|null $importOrigins
     * @throws AuthorDataException
     */
    public function create(
        AuthorFieldsDto $fields,
        ?array $realNames = null,
        ?array $groups = null,
        ?array $prods = null,
        ?array $importOrigins = null,
        ?UploadedFileDto $image = null,
    ): AuthorDataResultDto {
        return $this->createAuthorHandler->handle(new CreateAuthorCommand($fields, $realNames, $groups, $prods, $importOrigins, $image));
    }

    /**
     * Changes the fields the DTO carries and leaves the others as they are;
     * replaces each list given, and keeps a list left `null`.
     *
     * @param array<int, string>|null $realNames language id => the real name in it
     * @param list<MembershipInputDto>|null $groups
     * @param list<MembershipInputDto>|null $prods
     * @param list<ImportOriginDto>|null $importOrigins
     * @throws AuthorDataException
     */
    public function update(
        int $authorId,
        AuthorFieldsDto $fields,
        ?array $realNames = null,
        ?array $groups = null,
        ?array $prods = null,
        ?array $importOrigins = null,
        ?UploadedFileDto $image = null,
        bool $removeImage = false,
    ): AuthorDataResultDto {
        return $this->updateAuthorHandler->handle(new UpdateAuthorCommand($authorId, $fields, $realNames, $groups, $prods, $importOrigins, $image, $removeImage));
    }

    /**
     * @throws AuthorDataException
     */
    public function delete(int $authorId): AuthorDataResultDto
    {
        return $this->deleteAuthorHandler->handle(new DeleteAuthorCommand($authorId));
    }

    /**
     * @throws AuthorDataException
     */
    public function convertToGroup(int $authorId): AuthorDataResultDto
    {
        return $this->convertAuthorToGroupHandler->handle(new ConvertAuthorToGroupCommand($authorId));
    }

    /**
     * @throws AuthorDataException
     */
    public function join(int $authorId, int $joinedId, bool $asAlias = false): AuthorDataResultDto
    {
        return $this->joinAuthorsHandler->handle(new JoinAuthorsCommand($authorId, $joinedId, $asAlias));
    }
}
