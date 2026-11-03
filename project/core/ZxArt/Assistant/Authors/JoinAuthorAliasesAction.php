<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\UseCases\JoinAuthorAliasesCommand;
use ZxArt\Authors\UseCases\JoinAuthorAliasesHandler;
use ZxArt\Shared\EntityType;

/**
 * Merges a duplicate author alias into the main one; the plan shows the duplicate.
 *
 * @psalm-api
 */
final readonly class JoinAuthorAliasesAction implements WriteAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
        private JoinAuthorAliasesHandler $joinAuthorAliasesHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'join_author_aliases';
    }

    #[Override]
    public function description(): string
    {
        return 'Merges a duplicate alias into the main one: its credits move, and the joined alias is deleted.';
    }

    #[Override]
    public function schema(): string
    {
        return JoinAuthorAliasesArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Join;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::AuthorAlias;
    }

    #[Override]
    public function state(Arguments $arguments): AuthorAliasDto
    {
        $argumentsDto = JoinAuthorAliasesArgumentsDto::from($arguments);

        return $this->authorDtos->alias($argumentsDto->joinedId);
    }

    #[Override]
    public function write(Arguments $arguments): AuthorAliasDataResultDto
    {
        $argumentsDto = JoinAuthorAliasesArgumentsDto::from($arguments);

        return $this->joinAuthorAliasesHandler->handle(new JoinAuthorAliasesCommand($argumentsDto->mainId, $argumentsDto->joinedId));
    }
}
