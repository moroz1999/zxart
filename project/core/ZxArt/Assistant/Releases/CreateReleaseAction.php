<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Shared\EntityType;

/**
 * Creates a release of a production.
 *
 * @psalm-api
 */
final readonly class CreateReleaseAction implements WriteAction
{
    public function __construct(
        private CreateReleaseHandler $createReleaseHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_release';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates a release of the production prodId names, with the fields given. Set its file with '
            . 'set_release_file and credit its authors with set_release_author afterwards.';
    }

    #[Override]
    public function schema(): string
    {
        return CreateReleaseArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Create;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Release;
    }

    #[Override]
    public function state(Arguments $arguments): null
    {
        return null;
    }

    #[Override]
    public function write(Arguments $arguments): ReleaseDataResultDto
    {
        $argumentsDto = CreateReleaseArgumentsDto::from($arguments);

        return $this->createReleaseHandler->handle(new CreateReleaseCommand(new ReleaseFieldsDto(
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
