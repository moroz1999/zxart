<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\UseCases\DeleteProdCommand;
use ZxArt\Prods\UseCases\DeleteProdHandler;
use ZxArt\Shared\EntityType;

/**
 * Deletes a production.
 *
 * @psalm-api
 */
final readonly class DeleteProdAction implements WriteAction
{
    public function __construct(
        private ProdDtos $prodDtos,
        private DeleteProdHandler $deleteProdHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_prod';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a production together with its releases and files.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Delete;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Prod;
    }

    #[Override]
    public function state(Arguments $arguments): ProdDto
    {
        return $this->prodDtos->prod(IdArgumentsDto::from($arguments)->id);
    }

    #[Override]
    public function write(Arguments $arguments): ProdDataResultDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->deleteProdHandler->handle(new DeleteProdCommand($argumentsDto->id));
    }
}
