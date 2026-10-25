<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupAliasElement;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;

/**
 * Creates a group alias on behalf of the current user: the element in the letter its name starts with,
 * its fields, and the privileges that let its creator keep editing it. Checks no
 * privilege of its own — the caller does, and refreshes the user's privileges
 * once it is done creating.
 *
 * @psalm-api
 */
final readonly class GroupAliasCreator
{
    /** @var list<array{string, string}> structure type, action */
    private const array CREATOR_PRIVILEGES = [
        ['groupAlias', 'showPublicForm'],
        ['groupAlias', 'publicReceive'],
        ['groupAlias', 'publicDelete'],
    ];

    public function __construct(
        private GroupAliasRepository $groupAliasRepository,
        private GroupAliasFieldsWriter $groupAliasFieldsWriter,
        private CreatorPrivilegesGranter $creatorPrivilegesGranter,
    ) {
    }

    public function create(GroupAliasFieldsDto $fields): groupAliasElement
    {
        $name = $fields->title ?? '';
        if ($name === '') {
            throw new GroupAliasDataException('An alias needs a name', 400);
        }
        if ($fields->groupId === null || $fields->groupId <= 0) {
            throw new GroupAliasDataException('An alias needs its group', 400);
        }
        $element = $this->groupAliasRepository->create($name);
        $this->completeCreation($element, $fields);

        return $element;
    }

    /**
     * Fills and grants an element that was created but not yet written — by
     * {@see create()}, or by the legacy form, which creates its own.
     */
    public function completeCreation(groupAliasElement $element, GroupAliasFieldsDto $fields): void
    {
        $elementId = $element->getPersistedId();
        $this->groupAliasFieldsWriter->apply($element, $fields);
        $this->creatorPrivilegesGranter->grant($elementId, self::CREATOR_PRIVILEGES);
    }
}
