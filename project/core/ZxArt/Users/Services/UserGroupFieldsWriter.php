<?php

declare(strict_types=1);

namespace ZxArt\Users\Services;

use userGroupElement;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Users\Dto\UserGroupFieldsDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\Repositories\UserGroupRepository;

/**
 * Writes a user group's name and description; the structure name follows the
 * name, as the admin form has always set it.
 *
 * @psalm-api
 */
final readonly class UserGroupFieldsWriter
{
    public function __construct(
        private UserGroupRepository $userGroupRepository,
    ) {
    }

    /**
     * @throws UserDataException 400
     */
    public function assertValid(UserGroupFieldsDto $fields): void
    {
        if (trim($fields->groupName) === '') {
            throw new UserDataException('A user group needs a name', 400);
        }
    }

    public function apply(userGroupElement $group, UserGroupFieldsDto $fields): void
    {
        $groupName = trim($fields->groupName);
        ElementFields::assign($group, [
            'groupName' => $groupName,
            'description' => trim($fields->description),
        ]);
        $group->structureName = $groupName;
        $this->userGroupRepository->save($group);
    }
}
