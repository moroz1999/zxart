<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use cityElement;
use countryElement;
use groupElement;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;

/**
 * Writes the editable fields of a group and what follows from them: a renamed
 * group moves to the letter its name starts with, the subgroup links follow the
 * subgroup list, and the counters are recalculated. Only the fields the DTO
 * carries are touched, and every reference is checked before anything is written.
 *
 * Checks no privilege and writes no log — the caller does.
 *
 * @psalm-api
 */
final readonly class GroupFieldsWriter
{
    public function __construct(
        private EntityReferenceResolver $entityReferenceResolver,
        private GroupRepository $groupRepository,
    ) {
    }

    public function apply(groupElement $group, GroupFieldsDto $fields): void
    {
        $this->checkReferences($fields);

        ElementFields::assign($group, [
            'title' => $fields->title,
            'structureName' => $fields->title,
            'abbreviation' => $fields->abbreviation,
            'type' => $fields->type,
            'country' => $fields->countryId,
            'city' => $fields->cityId,
            'website' => $fields->website,
            'wikiLink' => $fields->wikiLink,
            'slogan' => $fields->slogan,
            'startDate' => $fields->startDate,
            'endDate' => $fields->endDate,
            'subGroupsSelector' => $fields->subGroupIds,
        ]);
        $this->groupRepository->save($group);
        if ($fields->title !== null) {
            $group->checkParentLetter();
        }
        if ($fields->subGroupIds !== null) {
            $group->persistSubGroupConnections();
        }
        $group->recalculate();
    }

    private function checkReferences(GroupFieldsDto $fields): void
    {
        try {
            if ($fields->countryId !== null && $fields->countryId > 0) {
                $this->entityReferenceResolver->element($fields->countryId, [countryElement::class], 'country');
            }
            if ($fields->cityId !== null && $fields->cityId > 0) {
                $this->entityReferenceResolver->element($fields->cityId, [cityElement::class], 'city');
            }
            $this->entityReferenceResolver->elements($fields->subGroupIds ?? [], [groupElement::class], 'subgroup');
        } catch (InvalidReferenceException $exception) {
            throw new GroupDataException($exception->getMessage(), 400, $exception);
        }
    }
}
