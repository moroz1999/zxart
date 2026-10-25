<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupAliasElement;
use groupElement;
use structureManager;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;

/**
 * Writes the editable fields of a group alias. Only the fields the DTO carries
 * are touched, and the group is checked before anything is written.
 *
 * Checks no privilege and writes no log — the caller does.
 *
 * @psalm-api
 */
final readonly class GroupAliasFieldsWriter
{
    public function __construct(
        private EntityReferenceResolver $entityReferenceResolver,
        private GroupAliasRepository $groupAliasRepository,
        private structureManager $structureManager,
    ) {
    }

    public function apply(groupAliasElement $alias, GroupAliasFieldsDto $fields): void
    {
        $previousOwnerId = ElementFields::number($alias, 'groupId');
        if ($fields->groupId !== null) {
            try {
                $this->entityReferenceResolver->element($fields->groupId, [groupElement::class], 'group');
            } catch (InvalidReferenceException $exception) {
                throw new GroupAliasDataException($exception->getMessage(), 400, $exception);
            }
        }

        ElementFields::assign($alias, [
            'title' => $fields->title,
            'structureName' => $fields->title,
            'groupId' => $fields->groupId,
            'startDate' => $fields->startDate,
            'endDate' => $fields->endDate,
        ]);
        $this->groupAliasRepository->save($alias);
        if ($fields->groupId !== null) {
            foreach (array_unique([$previousOwnerId, $fields->groupId]) as $ownerId) {
                $this->resetAliases($ownerId);
            }
        }
    }

    /**
     * The owner of a moved alias reads its aliases again, as an owner that
     * loaded them before would not see the change otherwise.
     */
    private function resetAliases(int $ownerId): void
    {
        $owner = $ownerId > 0 ? $this->structureManager->getElementById($ownerId) : null;
        if ($owner instanceof groupElement) {
            $owner->resetAliasElements();
        }
    }
}
