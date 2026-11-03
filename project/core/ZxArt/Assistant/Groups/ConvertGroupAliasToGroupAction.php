<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\UseCases\ConvertGroupAliasToGroupCommand;
use ZxArt\Groups\UseCases\ConvertGroupAliasToGroupHandler;
use ZxArt\Shared\EntityType;

/**
 * Turns a group alias into a group of its own.
 *
 * @psalm-api
 */
final readonly class ConvertGroupAliasToGroupAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private ConvertGroupAliasToGroupHandler $convertGroupAliasToGroupHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'convert_group_alias_to_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Turns an alias into a group of its own, of the same title, which takes its productions, publications, '
            . 'members and import origins; the alias is deleted.';
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
        return EntityType::GroupAlias;
    }

    #[Override]
    public function state(Arguments $arguments): GroupAliasDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->groupDtos->alias($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): GroupAliasDataResultDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->convertGroupAliasToGroupHandler->handle(new ConvertGroupAliasToGroupCommand($argumentsDto->id));
    }
}
