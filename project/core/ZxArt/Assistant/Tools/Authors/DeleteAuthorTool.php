<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Authors;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\Previews\Quantities;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Services\AuthorStateService;
use ZxArt\Authors\UseCases\DeleteAuthorCommand;
use ZxArt\Authors\UseCases\DeleteAuthorHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class DeleteAuthorTool implements WriteTool
{
    public function __construct(
        private DeleteAuthorHandler $deleteAuthorHandler,
        private AuthorStateService $authorStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes an author.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->deleteAuthorHandler->check($this->command($request));
        $state = $this->authorStateService->get($request->id);
        $text = (new EntityMentionDto(EntityType::Author, $request->id))->text();
        $credits = Quantities::phrase([
            [$state->counters->prods, 'production', 'productions'],
            [$state->counters->pictures, 'picture', 'pictures'],
            [$state->counters->tunes, 'tune', 'tunes'],
        ]);
        $aliases = Quantities::phrase([[count($state->aliasIds), 'alias', 'aliases']]);
        $consequences = [];
        if ($credits !== null) {
            $consequences[] = "$text is credited on $credits";
        }
        if ($aliases !== null) {
            $consequences[] = "It has $aliases";
        }

        return new ChangePreviewDto(
            kind: ChangeKind::Delete,
            target: new ChangeTargetDto(EntityType::Author, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AuthorDataResultDto
    {
        return $this->deleteAuthorHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): DeleteAuthorCommand
    {
        return new DeleteAuthorCommand($request->id);
    }
}
