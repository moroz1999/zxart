<?php

namespace ZxArt\Groups\Services;

use authorElement;
use ConfigManager;
use ElementsManager;
use groupAliasElement;
use groupElement;
use Illuminate\Database\Connection;
use LanguagesManager;
use LettersElementsListProviderTrait;
use linksManager;
use privilegesManager;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\GroupLabel;
use ZxArt\Import\Labels\LabelResolver;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\LinkTypes;
use ZxArt\Shared\EntityType;

class GroupsService extends ElementsManager
{
    protected const string TABLE = 'module_group';

    use LettersElementsListProviderTrait;

    protected array $columnRelations = [];

    public function __construct(
        protected LabelResolver           $labelResolver,
        protected linksManager            $linksManager,
        protected structureManager        $structureManager,
        protected ConfigManager           $configManager,
        protected privilegesManager       $privilegesManager,
        protected AuthorshipDbal    $authorshipDbal,
        protected languagesManager        $languagesManager,
        protected Connection              $db,
        private readonly ImportIdOperator $importIdOperator,

    )
    {
        $this->columnRelations = [
            'title' => ['title' => true],
            'date' => ['id' => true],
        ];
    }

    public function manufactureGroupElement(string $title): ?groupElement
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

    public function manufactureAliasElement(string $title = ''): ?groupAliasElement
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
                foreach ($authorElement->getAuthorshipRecords(EntityType::Prod->value) as $record) {
                    if ($zxProd = $this->structureManager->getElementById($record['elementId'])) {
                        $this->linksManager->linkElements($groupElement->id, $zxProd->getPersistedId(), 'zxProdGroups');
                        $this->structureManager->clearElementCache($groupElement->id);
                    }
                }
                foreach ($authorElement->getAuthorshipRecords(EntityType::Release->value) as $record) {
                    if ($zxProd = $this->structureManager->getElementById($record['elementId'])) {
                        $this->linksManager->linkElements($groupElement->id, $zxProd->getPersistedId(), 'zxReleasePublishers');
                        $this->structureManager->clearElementCache($groupElement->id);
                    }
                }
                if ($records = $this->db->table('import_origin')
                    ->where('elementId', '=', $authorElement->id)
                    ->get()) {
                    foreach ($records as $record) {
                        if (!$this->db->table('import_origin')
                            ->where('type', '=', EntityType::Group->value)
                            ->where('importOrigin', '=', $record['importOrigin'])
                            ->where('importId', '=', $record['importId'])
                            ->limit(1)
                            ->get()) {
                            $this->db->table('import_origin')
                                ->where('elementId', '=', $authorElement->id)
                                ->update(
                                    [
                                        'elementId' => $groupElement->id,
                                        'type' => EntityType::Group->value,
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

    public function convertGroupAliasToGroup(groupAliasElement $groupAliasElement): ?groupElement
    {
        $groupElement = null;
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

    public function getGroupByLabel(GroupLabel $label, ImportOrigin $origin): groupElement|groupAliasElement|null
    {
        /** @var groupElement|null $element */
        $element = $this->importIdOperator->getElementByImportId($label->id, $origin, EntityType::Group);
        if ($element === null) {
            $element = $this->labelResolver->resolveGroup($label);
        }
        return $element;
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

                if ($joinedElement instanceof groupElement) {
                    $joinedElement->resetAliasElements();
                }
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
                if ($existingGroupShipRecords = $this->authorshipDbal->getAuthorsInfo($targetElement->id, EntityType::Group)) {
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