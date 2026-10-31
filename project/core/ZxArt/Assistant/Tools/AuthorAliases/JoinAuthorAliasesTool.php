<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\AuthorAliases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\Quantities;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Services\AuthorAliasStateService;
use ZxArt\Authors\UseCases\JoinAuthorAliasesCommand;
use ZxArt\Authors\UseCases\JoinAuthorAliasesHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<JoinAuthorAliasesRequestDto>
 *
 * @psalm-api
 */
final readonly class JoinAuthorAliasesTool implements WriteTool
{
    public function __construct(
        private JoinAuthorAliasesHandler $joinAuthorAliasesHandler,
        private AuthorAliasStateService $authorAliasStateService,
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
    public function requestClass(): string
    {
        return JoinAuthorAliasesRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->joinAuthorAliasesHandler->check($this->command($request));
        $main = $this->authorAliasStateService->get($request->mainId);
        $joined = $this->authorAliasStateService->get($request->joinedId);
        $mainText = (new EntityMentionDto(EntityType::AuthorAlias, $request->mainId))->text();
        $joinedText = (new EntityMentionDto(EntityType::AuthorAlias, $request->joinedId))->text();
        $credits = Quantities::phrase([
            [$joined->counters->prods, 'production', 'productions'],
            [$joined->counters->pictures, 'picture', 'pictures'],
            [$joined->counters->tunes, 'tune', 'tunes'],
        ]);
        $consequences = ["$joinedText is deleted"];
        if ($credits !== null) {
            $consequences[] = "Its credits on $credits move to $mainText";
        }

        return new ChangePreviewDto(
            kind: ChangeKind::Join,
            target: new ChangeTargetDto(EntityType::AuthorAlias, $request->mainId, (string)$main->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $main->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AuthorAliasDataResultDto
    {
        return $this->joinAuthorAliasesHandler->handle($this->command($request));
    }

    private function command(JoinAuthorAliasesRequestDto $request): JoinAuthorAliasesCommand
    {
        return new JoinAuthorAliasesCommand($request->mainId, $request->joinedId);
    }
}
