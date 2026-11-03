<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\UseCases\DeleteReleaseCommand;
use ZxArt\Releases\UseCases\DeleteReleaseHandler;
use ZxArt\Shared\EntityType;

/**
 * Deletes a release.
 *
 * @psalm-api
 */
final readonly class DeleteReleaseAction implements WriteAction
{
    public function __construct(
        private ReleaseDtos $releaseDtos,
        private DeleteReleaseHandler $deleteReleaseHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_release';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a release together with its file and files.';
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
        return EntityType::Release;
    }

    #[Override]
    public function state(Arguments $arguments): ReleaseDto
    {
        return $this->releaseDtos->release(IdArgumentsDto::from($arguments)->id);
    }

    #[Override]
    public function write(Arguments $arguments): ReleaseDataResultDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->deleteReleaseHandler->handle(new DeleteReleaseCommand($argumentsDto->id));
    }
}
