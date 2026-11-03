<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\UseCases\JoinProdsCommand;
use ZxArt\Prods\UseCases\JoinProdsHandler;
use ZxArt\Shared\EntityType;

/**
 * Merges a duplicate production into the main one; the plan shows the duplicate.
 *
 * @psalm-api
 */
final readonly class JoinProdsAction implements WriteAction
{
    public function __construct(
        private ProdDtos $prodDtos,
        private JoinProdsHandler $joinProdsHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'join_prods';
    }

    #[Override]
    public function description(): string
    {
        return 'Merges a duplicate production into the main one: its releases and materials move, and unless '
            . 'releasesOnly its authors, groups, publishers, categories and the fields the main one lacks; the joined '
            . 'production is deleted.';
    }

    #[Override]
    public function schema(): string
    {
        return JoinProdsArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Join;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Prod;
    }

    #[Override]
    public function state(Arguments $arguments): ProdDto
    {
        return $this->prodDtos->prod(JoinProdsArgumentsDto::from($arguments)->joinedId);
    }

    #[Override]
    public function write(Arguments $arguments): ProdDataResultDto
    {
        $argumentsDto = JoinProdsArgumentsDto::from($arguments);

        return $this->joinProdsHandler->handle(new JoinProdsCommand($argumentsDto->mainId, $argumentsDto->joinedId, $argumentsDto->releasesOnly));
    }
}
