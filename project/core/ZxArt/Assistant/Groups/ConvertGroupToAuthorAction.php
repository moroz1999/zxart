<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\UseCases\ConvertGroupToAuthorCommand;
use ZxArt\Groups\UseCases\ConvertGroupToAuthorHandler;
use ZxArt\Shared\EntityType;

/**
 * Turns a group into an author.
 *
 * @psalm-api
 */
final readonly class ConvertGroupToAuthorAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private ConvertGroupToAuthorHandler $convertGroupToAuthorHandler,
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
        return 'Turns a group that is really one person into an author of the same title, credited on its productions and '
            . 'published releases; the group is deleted.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Convert;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Group;
    }

    #[Override]
    public function state(Arguments $arguments): GroupDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->groupDtos->group($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): GroupDataResultDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->convertGroupToAuthorHandler->handle(new ConvertGroupToAuthorCommand($argumentsDto->id));
    }
}
