<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\UseCases\UpdateReleaseCommand;
use ZxArt\Releases\UseCases\UpdateReleaseHandler;
use ZxArt\Shared\EntityType;

/**
 * Changes the given fields of a release.
 *
 * @psalm-api
 */
final readonly class UpdateReleaseAction implements WriteAction
{
    public function __construct(
        private ReleaseDtos $releaseDtos,
        private UpdateReleaseHandler $updateReleaseHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_release';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of a release that are given and leaves the others; a list given replaces the whole '
            . 'list, an empty string or list clears the field.';
    }

    #[Override]
    public function schema(): string
    {
        return UpdateReleaseArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Update;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Release;
    }

    #[Override]
    public function state(Arguments $arguments): ReleaseDto
    {
        return $this->releaseDtos->release(UpdateReleaseArgumentsDto::from($arguments)->id);
    }

    #[Override]
    public function write(Arguments $arguments): ReleaseDataResultDto
    {
        $argumentsDto = UpdateReleaseArgumentsDto::from($arguments);

        return $this->updateReleaseHandler->handle(new UpdateReleaseCommand($argumentsDto->id, new ReleaseFieldsDto(
            title: $argumentsDto->title,
            year: $argumentsDto->year,
            version: $argumentsDto->version,
            description: $argumentsDto->description,
            releaseType: $argumentsDto->releaseType,
            releaseFormats: $argumentsDto->releaseFormats,
            languages: $argumentsDto->languages,
            hardware: $argumentsDto->hardware,
            publisherIds: $argumentsDto->publisherIds,
            prodId: $argumentsDto->prodId,
        )));
    }
}
