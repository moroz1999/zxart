<?php

namespace ZxArt\Groups\Services;

use authorElement;
use ConfigManager;
use CountriesManager;
use ElementsManager;
use groupAliasElement;
use groupElement;
use Illuminate\Database\Connection;
use ImportIdOperatorTrait;
use LanguagesManager;
use LettersElementsListProviderTrait;
use linksManager;
use privilegesManager;
use structureManager;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\LinkTypes;

class GroupsService extends ElementsManager
{
    protected const string TABLE = 'module_group';

    use LettersElementsListProviderTrait;
    use ImportIdOperatorTrait;

    protected array $columnRelations = [];

    public function __construct(
        protected LabelResolver        $labelResolver,
        protected linksManager         $linksManager,
        protected structureManager     $structureManager,
        protected CountriesManager     $countriesManager,
        protected ConfigManager        $configManager,
        protected privilegesManager    $privilegesManager,
        protected AuthorshipRepository $authorshipRepository,
        protected languagesManager     $languagesManager,
        protected Connection           $db,
    )
    {
        $this->columnRelations = [
            'title' => ['title' => true],
            'date' => ['id' => true],
        ];
    }


    public function importGroup(GroupLabel $dto, ?string $origin = null, bool $createNew = true, ?array $memberNames = null): groupAliasElement|groupElement|null
    {
        $label = new GroupLabel(
            id: $dto->id ?? null,
            name: $dto->title,
            city: $dto->cityName ?? null,
            country: $dto->countryName ?? null,
            isAlias: $dto->isAlias ?? null,
            memberNames: $memberNames,
            parentGroupIds: $dto->groupsData ?? null
        );

        /** @var groupElement|groupAliasElement|null $element */
        $element = $this->getElementByImportId($label->id, $origin, 'group');
        if ($element === null) {
            $element = $this->labelResolver->resolve($label);

            if (($element !== null) && $origin !== null) {
                $this->saveImportId($element->id, $label->id, $origin, 'group');
            }
        }
        if ($createNew === false && $element === null) {
            return null;
        }

        if ($element === null) {
            if ($label->isAlias) {
                $element = $this->createGroupAlias($dto, $origin);
            } else {
                $element = $this->createGroup($dto, $origin);
            }
        }

        if ($element instanceof groupAliasElement) {
            $this->updateGroupAlias($element, $dto, $origin);
        } else {
            $this->updateGroup($element, $dto, $origin);
        }

        return $element;
    }

    private function createGroup(GroupLabel $dto, string $origin): ?groupElement
    {
        if ($element = $this->manufactureGroupElement($dto->title ?? '')) {
            $this->updateGroup($element, $dto, $origin);
            if ($dto->id !== null) {
                $this->saveImportId($element->id, (string)$dto->id, $origin, 'group');
            }
        }
        return $element ?? null;
    }

    private function manufactureGroupElement(string $title): ?groupElement
    {
        if ($letterId = $this->getLetterId($title)) {
            if ($letterElement = $this->structureManager->getElementById($letterId)) {
                /**
                 * @var groupElement $element
                 */
                if ($element = $this->structureManager->createElement('group', 'show', $letterElement->id)) {
                    return $element;
                }
            }
        }
        return null;
    }

    private function updateGroup(groupElement $element, GroupLabel $dto, string $origin): void
    {
        $changed = false;

        if (!empty($dto->title) && $element->title !== $dto->title && !$element->title) {
            $changed = true;
            $element->title = $dto->title;
            $element->structureName = $dto->title;
        }

        if (!empty($dto->type) && $element->type !== $dto->type && !$element->type) {
            $changed = true;
            $element->type = $dto->type;
        }

        if (!empty($dto->abbreviation) && $element->abbreviation !== $dto->abbreviation && !$element->abbreviation) {
            $changed = true;
            $element->abbreviation = $dto->abbreviation;
        }

        if (!empty($dto->website) && $element->website !== $dto->website && !$element->website) {
            $changed = true;
            $element->website = $dto->website;
        }

        if (!empty($dto->type) && $element->type !== $dto->type) {
            if (!$element->type || $element->type === 'unknown') {
                $changed = true;
                $element->type = $dto->type;
            }
        }

        if (!empty($dto->locationName)) {
            if ($locationElement = $this->countriesManager->getLocationByName($dto->locationName)) {
                if ($locationElement->structureType === 'country') {
                    if ($element->country != $locationElement->id) {
                        $changed = true;
                        $element->country = $locationElement->id;
                    }
                } elseif ($locationElement->structureType === 'city') {
                    if ($element->city != $locationElement->id) {
                        $changed = true;
                        $element->city = $locationElement->id;
                        if ($countryId = $locationElement->getCountryId()) {
                            $element->country = $countryId;
                        }
                    }
                }
            }
        }

        if (!empty($dto->countryName)) {
            if ($locationElement = $this->countriesManager->getLocationByName($dto->countryName)) {
                if ($locationElement->structureType === 'country') {
                    if ($element->country != $locationElement->id) {
                        $changed = true;
                        $element->country = $locationElement->id;
                    }
                }
            }
        }

        if (!empty($dto->cityName)) {
            if ($locationElement = $this->countriesManager->getLocationByName($dto->cityName)) {
                if ($locationElement->structureType === 'city') {
                    if ($element->city != $locationElement->id) {
                        $changed = true;
                        $element->city = $locationElement->id;
                    }
                }
            }
        }

        if (!empty($dto->countryId)) {
            $locationElement = $this->getElementByImportId((string)$dto->countryId, $origin, 'country');
            if ($locationElement && $element->country != $locationElement->id) {
                $changed = true;
                $element->country = $locationElement->id;
            }
        }

        if (is_array($dto->groupsData)) {
            $parentGroups = $element->parentGroups;
            foreach ($dto->groupsData as $groupId) {
                $groupElement = $this->getElementByImportId((string)$groupId, $origin, 'group');
                if ($groupElement !== null && !in_array($groupElement, $parentGroups, true)) {
                    $parentGroups[] = $groupElement;
                }
            }
            $changed = true;
            $element->parentGroups = $parentGroups;
        }


        if ($changed) {
            $element->checkCountry();
            $element->persistElementData();
        }
    }

    private function createGroupAlias(GroupLabel $dto, string $origin): ?groupAliasElement
    {
        if ($element = $this->manufactureAliasElement($dto->title ?? '')) {
            $this->updateGroupAlias($element, $dto, $origin);
            if ($dto->id !== null) {
                $this->saveImportId($element->id, (string)$dto->id, $origin, 'group');
            }
        }
        return $element ?? null;
    }

    private function manufactureAliasElement(string $title = ''): ?groupAliasElement
    {
        /**
         * @var groupAliasElement $element
         */
        if (($letterId = $this->getLetterId($title)) &&
            ($letterElement = $this->structureManager->getElementById($letterId)) &&
            ($element = $this->structureManager->createElement('groupAlias', 'show', $letterElement->id))
        ) {
            return $element;
        }
        return null;
    }

    private function updateGroupAlias(groupAliasElement $element, GroupLabel $groupAlias, string $origin): void
    {
        $changed = false;

        if (!empty($groupAlias->title) && $element->title != $groupAlias->title) {
            if (!$element->title) {
                $changed = true;
                $element->title = $groupAlias->title;
                $element->structureName = $groupAlias->title;
            }
        }

        if (!empty($groupAlias->groupId) && !$element->groupId) {
            if ($groupElement = $this->getElementByImportId((string)$groupAlias->groupId, $origin, 'group')) {
                $changed = true;
                $element->groupId = $groupElement->getId();
            }
        }
        if ($changed) {
            $element->persistElementData();
        }
    }

    public function convertAuthorToGroup(authorElement $authorElement): ?groupElement
    {
        $groupElement = null;
        if ($authorElement->structureType === 'author') {
            if ($groupElement = $this->manufactureGroupElement($authorElement->title)) {
                $groupElement->title = $authorElement->title;
                $groupElement->structureName = $authorElement->title;
                $groupElement->country = $authorElement->country;
                $groupElement->city = $authorElement->city;
                $groupElement->persistElementData();

                foreach ($authorElement->mentions as $article) {
                    $this->linksManager->linkElements($groupElement->id, $article->id, LinkTypes::PRESS_GROUPS->value);
                    $this->structureManager->clearElementCache($article->id);
                }
                foreach ($authorElement->getPublisherProds() as $zxProd) {
                    $this->linksManager->linkElements($groupElement->id, $zxProd->id, 'zxProdPublishers');
                    $this->structureManager->clearElementCache($zxProd->id);
                }
                foreach ($authorElement->getAuthorshipRecords('prod') as $record) {
                    if ($zxProd = $this->structureManager->getElementById($record['elementId'])) {
                        $this->linksManager->linkElements($groupElement->id, $zxProd->getId(), 'zxProdGroups');
                        $this->structureManager->clearElementCache($groupElement->id);
                    }
                }
                foreach ($authorElement->getAuthorshipRecords('release') as $record) {
                    if ($zxProd = $this->structureManager->getElementById($record['elementId'])) {
                        $this->linksManager->linkElements($groupElement->id, $zxProd->getId(), 'zxReleasePublishers');
                        $this->structureManager->clearElementCache($groupElement->id);
                    }
                }
                if ($records = $this->db->table('import_origin')
                    ->where('elementId', '=', $authorElement->id)
                    ->get()) {
                    foreach ($records as $record) {
                        if (!$this->db->table('import_origin')
                            ->where('type', '=', 'group')
                            ->where('importOrigin', '=', $record['importOrigin'])
                            ->where('importId', '=', $record['importId'])
                            ->limit(1)
                            ->get()) {
                            $this->db->table('import_origin')
                                ->where('elementId', '=', $authorElement->id)
                                ->update(
                                    [
                                        'elementId' => $groupElement->id,
                                        'type' => 'group',
                                    ]
                                );
                        } else {
                            $this->db->table('import_origin')
                                ->where('id', '=', $record['id'])->delete();
                        }
                    }
                }


                $authorElement->deleteElementData();
            }
        }
        return $groupElement;
    }


    /**
     * @param groupAliasElement $groupAliasElement
     * @return bool|groupElement
     */
    public function convertGroupAliasToGroup($groupAliasElement)
    {
        $groupElement = false;
        if ($groupAliasElement->structureType === 'groupAlias') {
            if ($groupElement = $this->manufactureGroupElement($groupAliasElement->title)) {
                $groupElement->title = $groupAliasElement->title;
                $groupElement->structureName = $groupAliasElement->title;
                $groupElement->country = $groupAliasElement->country;
                $groupElement->city = $groupAliasElement->city;
                $groupElement->persistElementData();

                foreach ($groupAliasElement->getPublisherProds() as $zxProd) {
                    $this->linksManager->linkElements($groupElement->id, $zxProd->getId(), 'zxProdPublishers');
                }
                foreach ($groupAliasElement->getGroupProds() as $zxProd) {
                    $this->linksManager->linkElements($groupElement->id, $zxProd->getId(), 'zxProdGroups');
                }

                $this->db->table('authorship')
                    ->where('elementId', '=', $groupAliasElement->id)
                    ->update(['elementId' => $groupElement->id]);

                $this->db->table('import_origin')
                    ->where('elementId', '=', $groupAliasElement->id)
                    ->update(['elementId' => $groupElement->id]);


                $groupAliasElement->deleteElementData();
            }
        }
        return $groupElement;
    }

    protected function getLettersListMarker(string $type): string
    {
        if ($type === 'admin') {
            return 'groups';
        }

        return 'groupsmenu';
    }

    public function joinDeleteGroup(int $mainGroupId, int $joinedGroupId): void
    {
        $this->joinGroup($mainGroupId, $joinedGroupId, false);
    }

    public function joinGroupAsAlias(int $mainGroupId, int $joinedGroupId): void
    {
        $this->joinGroup($mainGroupId, $joinedGroupId, true);
    }

    protected function joinGroup($targetId, $joinedId, bool $makeAlias = true): bool
    {
        if ($joinedId == $targetId) {
            return false;
        }
        /**
         * @var groupAliasElement|groupElement $joinedElement
         */
        if ($joinedElement = $this->structureManager->getElementById($joinedId)) {
            /**
             * @var groupElement $targetGroupElement
             */
            $targetGroupElement = false;
            /**
             * @var groupAliasElement|groupElement $targetElement
             */
            if ($targetElement = $this->structureManager->getElementById($targetId)) {
                if ($targetElement->structureType === 'groupAlias') {
                    $targetGroupElement = $targetElement->getGroupElement();
                } elseif ($targetElement->structureType === 'group') {
                    $targetGroupElement = $targetElement;
                }
                if ($makeAlias) {
                    $targetElement = $this->manufactureAliasElement($joinedElement->title);
                    $targetElement->title = $joinedElement->title;
                    $targetElement->structureName = $joinedElement->title;
                    $targetElement->groupId = $targetGroupElement->id;
                }
            }
            if ($targetElement && $targetGroupElement) {
                if ($joinedElement->structureType === 'group') {
                    if (!$targetGroupElement->country) {
                        $targetGroupElement->country = $joinedElement->country;
                    }
                    if (!$targetGroupElement->city) {
                        $targetGroupElement->city = $joinedElement->city;
                    }
                    if (!$targetGroupElement->website) {
                        $targetGroupElement->website = $joinedElement->website;
                    }
                    if (!$targetGroupElement->wikiLink) {
                        $targetGroupElement->wikiLink = $joinedElement->wikiLink;
                    }
                    if (!$targetGroupElement->startDate) {
                        $targetGroupElement->startDate = $joinedElement->startDate;
                    }
                    if (!$targetGroupElement->endDate) {
                        $targetGroupElement->endDate = $joinedElement->endDate;
                    }
                    if (!$targetGroupElement->slogan) {
                        $targetGroupElement->slogan = $joinedElement->slogan;
                    }
                    if ($joinedElement->type !== 'unknown' && $targetGroupElement->type === 'unknown') {
                        $targetGroupElement->type = $joinedElement->type;
                    }
                    $targetGroupElement->persistElementData();
                }
                $targetElement->persistElementData();

                if ($aliasElements = $joinedElement->getAliasElements()) {
                    /**
                     * @var groupAliasElement $aliasElement
                     */
                    foreach ($aliasElements as $aliasElement) {
                        $aliasElement->groupId = $targetGroupElement->id;
                        $aliasElement->persistElementData();
                    }
                }

                $this->privilegesManager->copyPrivileges($joinedElement->id, $targetGroupElement->id);

                if ($links = $this->linksManager->getElementsLinks($joinedId, null, 'parent')) {
                    foreach ($links as $link) {
                        $this->linksManager->unLinkElements($joinedId, $link->childStructureId, $link->type);
                        $this->linksManager->linkElements($targetElement->id, $link->childStructureId, $link->type);
                    }
                }


                //disabled groupship moving to new group
                //check if group already has groupship in same elements. we dont need duplicates
                $existingAuthorIds = [];
                if ($existingGroupShipRecords = $this->authorshipRepository->getAuthorsInfo($targetElement->id, 'group')) {
                    foreach ($existingGroupShipRecords as $record) {
                        $existingAuthorIds[] = $record['authorId'];
                    }
                }

                //delete duplicates from joined group
                if ($existingAuthorIds) {
                    $this->db->table('authorship')
                        ->where('elementId', '=', $joinedId)
                        ->whereIn('authorId', $existingAuthorIds)
                        ->delete();
                }

                //now move all remaining records to main group
                $this->db->table('authorship')
                    ->where('elementId', '=', $joinedId)
                    ->update(['elementId' => $targetElement->id]);

                $this->db->table('import_origin')
                    ->where('elementId', '=', $joinedId)
                    ->update(['elementId' => $targetElement->id]);

                $joinedElement->deleteElementData();
            }
        }
        return true;
    }

}