<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\FieldChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<CreateReleaseRequestDto>
 *
 * @psalm-api
 */
final readonly class CreateReleaseTool implements WriteTool
{
    public function __construct(
        private CreateReleaseHandler $createReleaseHandler,
        private FieldChanges $fieldChanges,
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
            . 'set_release_file and credit authors with set_release_author afterwards.';
    }

    #[Override]
    public function requestClass(): string
    {
        return CreateReleaseRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        return new ChangePreviewDto(
            kind: ChangeKind::Create,
            target: new ChangeTargetDto(EntityType::Release, null, (string)$request->fields->title),
            changes: $this->fieldChanges->diff(null, $request->fields),
            consequences: [],
            modifiedAt: null,
        );
    }

    #[Override]
    public function execute(object $request): ReleaseDataResultDto
    {
        return $this->createReleaseHandler->handle($this->command($request));
    }

    private function command(CreateReleaseRequestDto $request): CreateReleaseCommand
    {
        return new CreateReleaseCommand($request->fields);
    }
}
