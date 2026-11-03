<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\UseCases\SetProdMemberCommand;
use ZxArt\Prods\UseCases\SetProdMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * Credits an author on a production, or replaces the roles.
 *
 * @psalm-api
 */
final readonly class SetProdAuthorAction implements WriteAction
{
    public function __construct(
        private ProdDtos $prodDtos,
        private SetProdMemberHandler $setProdMemberHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'set_prod_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Credits an author or an author alias on a production with the roles given, or replaces the roles of '
            . 'one credited already.';
    }

    #[Override]
    public function schema(): string
    {
        return SetProdAuthorArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Link;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Prod;
    }

    #[Override]
    public function state(Arguments $arguments): ProdDto
    {
        return $this->prodDtos->prod(SetProdAuthorArgumentsDto::from($arguments)->prodId);
    }

    #[Override]
    public function write(Arguments $arguments): ProdDataResultDto
    {
        $argumentsDto = SetProdAuthorArgumentsDto::from($arguments);

        return $this->setProdMemberHandler->handle(new SetProdMemberCommand($argumentsDto->prodId, $argumentsDto->authorId, $argumentsDto->roles));
    }
}
