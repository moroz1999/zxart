<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Authors;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\Quantities;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Services\AuthorStateService;
use ZxArt\Authors\UseCases\JoinAuthorsCommand;
use ZxArt\Authors\UseCases\JoinAuthorsHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<JoinAuthorsRequestDto>
 *
 * @psalm-api
 */
final readonly class JoinAuthorsTool implements WriteTool
{
    public function __construct(
        private JoinAuthorsHandler $joinAuthorsHandler,
        private AuthorStateService $authorStateService,
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
        return 'Merges a duplicate author into the main one: its aliases, credits, memberships and import origins '
            . 'move, and the joined author is deleted; with asAlias its credits go to a new alias of the main '
            . 'author named as the joined one.';
    }

    #[Override]
    public function requestClass(): string
    {
        return JoinAuthorsRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $main = $this->authorStateService->get($request->mainId);
        $joined = $this->authorStateService->get($request->joinedId);
        $mainText = (new EntityMentionDto(EntityType::Author, $request->mainId))->text();
        $joinedText = (new EntityMentionDto(EntityType::Author, $request->joinedId))->text();
        $credits = Quantities::phrase([
            [$joined->counters->prods, 'production', 'productions'],
            [$joined->counters->pictures, 'picture', 'pictures'],
            [$joined->counters->tunes, 'tune', 'tunes'],
        ]);
        $consequences = ["$joinedText is deleted"];
        if ($request->asAlias) {
            $consequences[] = "A new alias of $mainText takes its title and its credits";
        } elseif ($credits !== null) {
            $consequences[] = "Its credits on $credits move to $mainText";
        }
        $consequences[] = "Its aliases, group memberships and import origins move to $mainText";

        return new ChangePreviewDto(
            kind: ChangeKind::Join,
            target: new ChangeTargetDto(EntityType::Author, $request->mainId, (string)$main->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $main->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AuthorDataResultDto
    {
        return $this->joinAuthorsHandler->handle($this->command($request));
    }

    private function command(JoinAuthorsRequestDto $request): JoinAuthorsCommand
    {
        return new JoinAuthorsCommand($request->mainId, $request->joinedId, $request->asAlias);
    }
}
