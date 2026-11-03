<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\UseCases\DeleteProdFileCommand;
use ZxArt\Prods\UseCases\DeleteProdFileHandler;
use ZxArt\Shared\EntityType;

/**
 * Deletes a file of a production.
 *
 * @psalm-api
 */
final readonly class DeleteProdFileAction implements WriteAction
{
    public function __construct(
        private ProdDtos $prodDtos,
        private DeleteProdFileHandler $deleteProdFileHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_prod_file';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a file from the file lists of a production.';
    }

    #[Override]
    public function schema(): string
    {
        return DeleteProdFileArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::DeleteFile;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Prod;
    }

    #[Override]
    public function state(Arguments $arguments): ProdDto
    {
        return $this->prodDtos->prod(DeleteProdFileArgumentsDto::from($arguments)->prodId);
    }

    #[Override]
    public function write(Arguments $arguments): ProdDataResultDto
    {
        $argumentsDto = DeleteProdFileArgumentsDto::from($arguments);

        return $this->deleteProdFileHandler->handle(new DeleteProdFileCommand($argumentsDto->prodId, $argumentsDto->fileId));
    }
}
