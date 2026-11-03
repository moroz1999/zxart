<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\UseCases\DeleteProdMemberCommand;
use ZxArt\Prods\UseCases\DeleteProdMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * Removes a credit from a production.
 *
 * @psalm-api
 */
final readonly class RemoveProdAuthorAction implements WriteAction
{
    public function __construct(
        private ProdDtos $prodDtos,
        private DeleteProdMemberHandler $deleteProdMemberHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'remove_prod_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Removes the credit of an author or an author alias from a production.';
    }

    #[Override]
    public function schema(): string
    {
        return RemoveProdAuthorArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Unlink;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Prod;
    }

    #[Override]
    public function state(Arguments $arguments): ProdDto
    {
        return $this->prodDtos->prod(RemoveProdAuthorArgumentsDto::from($arguments)->prodId);
    }

    #[Override]
    public function write(Arguments $arguments): ProdDataResultDto
    {
        $argumentsDto = RemoveProdAuthorArgumentsDto::from($arguments);

        return $this->deleteProdMemberHandler->handle(new DeleteProdMemberCommand($argumentsDto->prodId, $argumentsDto->authorId));
    }
}
