<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\AuthorAliases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Services\AuthorAliasStateService;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorCommand;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class ConvertAuthorAliasToAuthorTool implements WriteTool
{
    public function __construct(
        private ConvertAuthorAliasToAuthorHandler $convertAuthorAliasToAuthorHandler,
        private AuthorAliasStateService $authorAliasStateService,
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
        return 'Turns an alias into an author of its own, of the same title, which takes its credits, links and '
            . 'import origins; the alias is deleted.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->authorAliasStateService->get($request->id);
        $text = (new EntityMentionDto(EntityType::AuthorAlias, $request->id))->text();

        return new ChangePreviewDto(
            kind: ChangeKind::Convert,
            target: new ChangeTargetDto(EntityType::AuthorAlias, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: ["$text is deleted; a new author of the same title takes its credits, links and import origins"],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AuthorAliasDataResultDto
    {
        return $this->convertAuthorAliasToAuthorHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): ConvertAuthorAliasToAuthorCommand
    {
        return new ConvertAuthorAliasToAuthorCommand($request->id);
    }
}
