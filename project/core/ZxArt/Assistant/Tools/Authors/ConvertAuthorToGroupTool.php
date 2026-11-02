<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Authors;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Services\AuthorStateService;
use ZxArt\Authors\UseCases\ConvertAuthorToGroupCommand;
use ZxArt\Authors\UseCases\ConvertAuthorToGroupHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class ConvertAuthorToGroupTool implements WriteTool
{
    public function __construct(
        private ConvertAuthorToGroupHandler $convertAuthorToGroupHandler,
        private AuthorStateService $authorStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'convert_author_to_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Turns an author that is really a group into a group of the same title: the productions it is '
            . 'credited on become the group\'s, its releases the group\'s publications; the author is deleted.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->authorStateService->get($request->id);
        $text = (new EntityMentionDto(EntityType::Author, $request->id))->text();

        return new ChangePreviewDto(
            kind: ChangeKind::Convert,
            target: new ChangeTargetDto(EntityType::Author, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: ["$text is deleted; a new group of the same title takes its productions as made by the group, its releases as published by it, its publications and press mentions"],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AuthorDataResultDto
    {
        return $this->convertAuthorToGroupHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): ConvertAuthorToGroupCommand
    {
        return new ConvertAuthorToGroupCommand($request->id);
    }
}
