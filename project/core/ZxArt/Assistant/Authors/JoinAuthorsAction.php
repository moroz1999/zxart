<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\UseCases\JoinAuthorsCommand;
use ZxArt\Authors\UseCases\JoinAuthorsHandler;
use ZxArt\Shared\EntityType;

/**
 * Merges a duplicate author into the main one; the plan shows the duplicate.
 *
 * @psalm-api
 */
final readonly class JoinAuthorsAction implements WriteAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
        private JoinAuthorsHandler $joinAuthorsHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'join_authors';
    }

    #[Override]
    public function description(): string
    {
        return 'Merges a duplicate author into the main one: its aliases, credits, memberships and import origins move, '
            . 'and the joined author is deleted; with asAlias its credits go to a new alias of the main author named '
            . 'as the joined one.';
    }

    #[Override]
    public function schema(): string
    {
        return JoinAuthorsArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Join;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Author;
    }

    #[Override]
    public function state(Arguments $arguments): AuthorDto
    {
        $argumentsDto = JoinAuthorsArgumentsDto::from($arguments);

        return $this->authorDtos->author($argumentsDto->joinedId);
    }

    #[Override]
    public function write(Arguments $arguments): AuthorDataResultDto
    {
        $argumentsDto = JoinAuthorsArgumentsDto::from($arguments);

        return $this->joinAuthorsHandler->handle(new JoinAuthorsCommand(
            $argumentsDto->mainId,
            $argumentsDto->joinedId,
            $argumentsDto->asAlias,
        ));
    }
}
