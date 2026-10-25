<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\ProdFileKind;
use ZxArt\Prods\UseCases\AddProdFilesCommand;
use ZxArt\Prods\UseCases\AddProdFilesHandler;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Prods\UseCases\DeleteProdCommand;
use ZxArt\Prods\UseCases\DeleteProdFileCommand;
use ZxArt\Prods\UseCases\DeleteProdFileHandler;
use ZxArt\Prods\UseCases\DeleteProdHandler;
use ZxArt\Prods\UseCases\DeleteProdMemberCommand;
use ZxArt\Prods\UseCases\DeleteProdMemberHandler;
use ZxArt\Prods\UseCases\JoinProdsCommand;
use ZxArt\Prods\UseCases\JoinProdsHandler;
use ZxArt\Prods\UseCases\SetProdMemberCommand;
use ZxArt\Prods\UseCases\SetProdMemberHandler;
use ZxArt\Prods\UseCases\UpdateProdCommand;
use ZxArt\Prods\UseCases\UpdateProdHandler;
use ZxArt\Shared\Dto\AddedFilesDto;

/**
 * Changes to a production requested through `/prod-data/`, each one a use case
 * in `ZxArt\Prods\UseCases`: it checks the privilege of the matching legacy
 * action, runs in one transaction and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class ProdDataService
{
    public function __construct(
        private CreateProdHandler $createProdHandler,
        private UpdateProdHandler $updateProdHandler,
        private DeleteProdHandler $deleteProdHandler,
        private SetProdMemberHandler $setProdMemberHandler,
        private DeleteProdMemberHandler $deleteProdMemberHandler,
        private AddProdFilesHandler $addProdFilesHandler,
        private DeleteProdFileHandler $deleteProdFileHandler,
        private JoinProdsHandler $joinProdsHandler,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function create(ProdFieldsDto $fields): ProdDataResultDto
    {
        return $this->createProdHandler->handle(new CreateProdCommand($fields));
    }

    /**
     * Changes the fields the DTO carries and leaves the others as they are.
     *
     * @throws ProdDataException
     */
    public function update(int $prodId, ProdFieldsDto $fields): ProdDataResultDto
    {
        return $this->updateProdHandler->handle(new UpdateProdCommand($prodId, $fields));
    }

    /**
     * @throws ProdDataException
     */
    public function delete(int $prodId): ProdDataResultDto
    {
        return $this->deleteProdHandler->handle(new DeleteProdCommand($prodId));
    }

    /**
     * @param list<string> $roles none stands for an unknown role
     * @throws ProdDataException
     */
    public function setMember(int $prodId, int $authorId, array $roles): ProdDataResultDto
    {
        return $this->setProdMemberHandler->handle(new SetProdMemberCommand($prodId, $authorId, $roles));
    }

    /**
     * @throws ProdDataException
     */
    public function deleteMember(int $prodId, int $authorId): ProdDataResultDto
    {
        return $this->deleteProdMemberHandler->handle(new DeleteProdMemberCommand($prodId, $authorId));
    }

    /**
     * @param string[] $urls
     * @throws ProdDataException
     */
    public function addFiles(int $prodId, ProdFileKind $kind, array $urls): AddedFilesDto
    {
        return $this->addProdFilesHandler->handle(new AddProdFilesCommand($prodId, $kind, $urls));
    }

    /**
     * @throws ProdDataException
     */
    public function deleteFile(int $prodId, int $fileId): ProdDataResultDto
    {
        return $this->deleteProdFileHandler->handle(new DeleteProdFileCommand($prodId, $fileId));
    }

    /**
     * @throws ProdDataException
     */
    public function join(int $prodId, int $joinedId, bool $releasesOnly = false): ProdDataResultDto
    {
        return $this->joinProdsHandler->handle(new JoinProdsCommand($prodId, $joinedId, $releasesOnly));
    }
}
