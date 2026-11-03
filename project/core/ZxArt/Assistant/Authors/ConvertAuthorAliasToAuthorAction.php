<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorCommand;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorHandler;
use ZxArt\Shared\EntityType;

/**
 * Turns an author alias into an author of its own.
 *
 * @psalm-api
 */
final readonly class ConvertAuthorAliasToAuthorAction implements WriteAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
        private ConvertAuthorAliasToAuthorHandler $convertAuthorAliasToAuthorHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'convert_author_alias_to_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Turns an alias into an author of its own, of the same title, which takes its credits, links and import '
            . 'origins; the alias is deleted.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Convert;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::AuthorAlias;
    }

    #[Override]
    public function state(Arguments $arguments): AuthorAliasDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->authorDtos->alias($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): AuthorAliasDataResultDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->convertAuthorAliasToAuthorHandler->handle(new ConvertAuthorAliasToAuthorCommand($argumentsDto->id));
    }
}
