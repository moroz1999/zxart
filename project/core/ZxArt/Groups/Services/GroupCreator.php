<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupElement;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Shared\Elements\CreatorPrivilegesGranter;

/**
 * Creates a group on behalf of the current user: the element in the letter its name starts with,
 * its fields, and the privileges that let its creator keep editing it. Checks no
 * privilege of its own — the caller does, and refreshes the user's privileges
 * once it is done creating.
 *
 * @psalm-api
 */
final readonly class GroupCreator
{
    /** @var list<array{string, string}> structure type, action */
    private const array CREATOR_PRIVILEGES = [
        ['group', 'showPublicForm'],
        ['group', 'publicReceive'],
        ['group', 'publicDelete'],
        ['group', 'deleteFile'],
        ['group', 'deleteAuthor'],
    ];

    public function __construct(
        private GroupRepository $groupRepository,
        private GroupFieldsWriter $groupFieldsWriter,
        private CreatorPrivilegesGranter $creatorPrivilegesGranter,
    ) {
    }

    public function create(GroupFieldsDto $fields): groupElement
    {
        $name = $fields->title ?? '';
        if ($name === '') {
            throw new GroupDataException('A group needs a name', 400);
        }
        $element = $this->groupRepository->create($name);
        $this->completeCreation($element, $fields);

        return $element;
    }

    /**
     * Fills and grants an element that was created but not yet written — by
     * {@see create()}, or by the legacy form, which creates its own.
     */
    public function completeCreation(groupElement $element, GroupFieldsDto $fields): void
    {
        $elementId = $element->getPersistedId();
        $this->groupFieldsWriter->apply($element, $fields);
        $this->creatorPrivilegesGranter->grant($elementId, self::CREATOR_PRIVILEGES);
    }
}
