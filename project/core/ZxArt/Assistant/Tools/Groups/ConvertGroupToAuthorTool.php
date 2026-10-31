<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Groups;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Services\GroupStateService;
use ZxArt\Groups\UseCases\ConvertGroupToAuthorCommand;
use ZxArt\Groups\UseCases\ConvertGroupToAuthorHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class ConvertGroupToAuthorTool implements WriteTool
{
    public function __construct(
        private ConvertGroupToAuthorHandler $convertGroupToAuthorHandler,
        private GroupStateService $groupStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'convert_group_to_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Turns a group that is really one person into an author of the same title, credited on its '
            . 'productions and published releases; the group is deleted.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->convertGroupToAuthorHandler->check($this->command($request));
        $state = $this->groupStateService->get($request->id);
        $text = (new EntityMentionDto(EntityType::Group, $request->id))->text();

        return new ChangePreviewDto(
            kind: ChangeKind::Convert,
            target: new ChangeTargetDto(EntityType::Group, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: ["$text is deleted; a new author of the same title is credited on its productions and published releases and takes its publications and press mentions"],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupDataResultDto
    {
        return $this->convertGroupToAuthorHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): ConvertGroupToAuthorCommand
    {
        return new ConvertGroupToAuthorCommand($request->id);
    }
}
