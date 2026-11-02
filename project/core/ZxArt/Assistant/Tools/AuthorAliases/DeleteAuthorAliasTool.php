<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\AuthorAliases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\Previews\Quantities;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Services\AuthorAliasStateService;
use ZxArt\Authors\UseCases\DeleteAuthorAliasCommand;
use ZxArt\Authors\UseCases\DeleteAuthorAliasHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class DeleteAuthorAliasTool implements WriteTool
{
    public function __construct(
        private DeleteAuthorAliasHandler $deleteAuthorAliasHandler,
        private AuthorAliasStateService $authorAliasStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_author_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes an author alias.';
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
        $credits = Quantities::phrase([
            [$state->counters->prods, 'production', 'productions'],
            [$state->counters->pictures, 'picture', 'pictures'],
            [$state->counters->tunes, 'tune', 'tunes'],
        ]);
        $consequences = $credits !== null ? ["$text is credited on $credits"] : [];

        return new ChangePreviewDto(
            kind: ChangeKind::Delete,
            target: new ChangeTargetDto(EntityType::AuthorAlias, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AuthorAliasDataResultDto
    {
        return $this->deleteAuthorAliasHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): DeleteAuthorAliasCommand
    {
        return new DeleteAuthorAliasCommand($request->id);
    }
}
