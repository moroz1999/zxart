<?php

use App\Users\CurrentUser;
use App\Users\CurrentUserService;

class tagsManager extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    protected $elementsIndex = [];
    /** @var tagsManager */
    public static $instance = false;
    protected $collection;

    /**
     * @return tagsManager
     * @deprecated
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new tagsManager();
        }
        return self::$instance;
    }

    public function joinTags($parentTagId, $joinedTagId): void
    {
        if ($parentTagId != $joinedTagId) {
            if ($parentTagElement = $this->getTagElement($parentTagId)) {
                if ($joinedTagElement = $this->getTagElement($joinedTagId)) {
                    $linksManager = $this->getService('linksManager');
                    if ($links = $linksManager->getElementsLinks($joinedTagId, 'tagLink', 'parent')) {
                        foreach ($links as $link) {
                            $elementId = $link->childStructureId;
                            $linksManager->unLinkElements($joinedTagId, $elementId, 'tagLink');
                            $linksManager->linkElements($parentTagId, $elementId, 'tagLink');
                        }
                    }
                    $languagesManager = $this->getService('LanguagesManager');
                    foreach ($languagesManager->getLanguagesIdList('public_root') as $languageId) {
                        $joinedTitle = $joinedTagElement->getLanguageValue("title", $languageId);
                        $parentSynonym = $parentTagElement->getLanguageValue("synonym", $languageId);
                        if ($joinedTitle != $parentTagElement->getLanguageValue("title", $languageId)) {
                            if (!stripos(mb_strtolower($parentSynonym), mb_strtolower($joinedTitle))) {
                                if ($parentSynonym) {
                                    $parentSynonym .= ", " . $joinedTitle;
                                } else {
                                    $parentSynonym .= $joinedTitle;
                                }
                                $parentTagElement->setValue("synonym", $parentSynonym, $languageId);
                            }
                        }
                    }
                    $parentTagElement->updateAmount();
                    $parentTagElement->persistElementData();
                    $joinedTagElement->deleteElementData();
                }
            }
        }
    }

    public function getTagElement($id)
    {
        if (!isset($this->elementsIndex[$id])) {
            $this->elementsIndex[$id] = false;
            $structureManager = $this->getService('structureManager');
            if ($tagElement = $structureManager->getElementById($id)) {
                $this->elementsIndex[$id] = $tagElement;
            }
        }

        return $this->elementsIndex[$id];
    }

    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getElementSuggestedTags($elementId, $amount): array
    {
        $result = [];
        if (is_array($tagsIdList = $this->getTagsIdList($elementId))) {
            if ($suggestedIds = $this->getSuggestedTagsIds($tagsIdList, $amount)) {
                foreach ($suggestedIds as $tagId) {
                    if ($tag = $this->getTagElement($tagId)) {
                        $result[] = $tag;
                    }
                }
            }
        }
        $sort = [];
        foreach ($result as $tagElement) {
            $sort[] = mb_strtolower($tagElement->title);
        }
        array_multisort($sort, SORT_ASC, $result);

        return $result;
    }

    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getSuggestedTagsIds(array $idList, $amount = 10): array
    {
        $result = [];
        $collection = persistableCollection::getInstance('structure_links');

        if ($childIds = $this->getConnectedElementIds($idList)) {
            $counts = $collection->conditionalLoad(
                [
                    'parentStructureId',
                    'count(childStructureId)',
                ],
                [
                    ['childStructureId', 'in', $childIds],
                    ['parentStructureId', 'not in', $idList],
                    ['type', '=', 'tagLink'],
                ],
                ['count(childStructureId)' => 0],
                $amount,
                'parentStructureId',
                true
            );
            foreach ($counts as $row) {
                $result[] = $row['parentStructureId'];
            }
        }
        return $result;
    }

    /**
     * @param false $filterIdList
     *
     * @psalm-param list{mixed} $tagsIdList
     *
     * @psalm-return list<mixed>
     */
    public function getConnectedElementIds(array $tagsIdList, bool $filterIdList = false): array
    {
        $query = $this->getService('db')
            ->table('structure_links')
            ->whereIn('parentStructureId', $tagsIdList)
            ->where('type', '=', 'tagLink');


        if (is_array($filterIdList)) {
            $query->whereIn('childStructureId', $filterIdList);
        }
        $childIds = [];
        if ($records = $query->get(['childStructureId'])) {
            $childIds = array_column($records, 'childStructureId');
        }

        return $childIds;
    }

    public function getTagsIdList($elementId)
    {
        $linksManager = $this->getService('linksManager');
        $result = $linksManager->getConnectedIdList($elementId, 'tagLink', 'child');
        return $result;
    }

    public function getConnectedElementIdsByNames($names, bool $intersect = true)
    {
        //can't be array, used in getConnectedElementIds
        $result = false;
        if ($tags = $this->getTagElementsByNames($names, false)) {
            foreach ($tags as $tagElement) {
                if ($intersect) {
                    $result = $this->getConnectedElementIds([$tagElement->id], $result);
                } else {
                    if (!$result) {
                        $result = [];
                    }
                    $result = array_merge($result, $this->getConnectedElementIds([$tagElement->id]));
                }
            }
        }
        if ($result) {
            return $result;
        } else {
            return [];
        }
    }

    /**
     * @param false $createNew
     *
     * @psalm-return list{0?: mixed,...}
     */
    public function getTagElementsByNames($names, bool $createNew = false): array
    {
        $result = [];
        foreach ($names as $name) {
            if ($tagElement = $this->getTagElementByName($name, $createNew)) {
                $result[] = $tagElement;
            }
        }
        return $result;
    }

    public function getTagElementByName($tagName, bool $createNew = false): ?tagElement
    {
        $tagElement = null;
        $tagName = mb_convert_case(trim($tagName), MB_CASE_TITLE, "UTF-8");
        if ($tagName !== '') {
            if (!($tagElement = $this->loadTagElement($tagName)) && $createNew) {
                $tagElement = $this->createTagElement($tagName);
            }
        }
        return $tagElement;
    }

    public function loadTagElement(string $tagName): ?tagElement
    {
        $result = null;

        $query = $this->getService('db')
            ->table('module_tag')
            ->select('id')
            ->where('title', '=', $tagName)->limit(1);
        if ($records = $query->get()) {
            foreach ($records as $record) {
                $structureManager = $this->getService('structureManager');
                if ($result = $structureManager->getElementById($record['id'])) {
                    break;
                }
            }
        }

        return $result;
    }

    public function createTagElement(string $tagName): ?tagElement
    {
        $tagElement = null;
        $structureManager = $this->getService('structureManager');
        if ($tagsElementId = $structureManager->getElementIdByMarker('tags')) {
            if ($tagElement = $structureManager->createElement('tag', 'show', $structureManager->rootElementId)) {
                $tagElement->prepareActualData();
                $tagElement->structureName = $tagName;
                $tagElement->title = $tagName;
                $currentUserService = $this->getService(CurrentUserService::class);
                $tagElement->userId = $currentUserService->getCurrentUser()->id;
                $tagElement->persistElementData();
                $structureManager->moveElement($structureManager->rootElementId, $tagsElementId, $tagElement->id);
            }
        }
        return $tagElement;
    }

    public function addTag($tagName, $elementId): ?tagElement
    {
        if ($tagElement = $this->getTagElementByName($tagName, true)) {
            $this->getService('linksManager')->linkElements($tagElement->id, $elementId, 'tagLink', true);
            $tagElement->updateAmount();
        }
        return $tagElement;
    }

    public function removeTag($tagName, $elementId): void
    {
        if ($tagElement = $this->getTagElementByName($tagName, false)) {
            $this->getService('linksManager')->unLinkElements($tagElement->id, $elementId, 'tagLink');
            $tagElement->updateAmount();
        }
    }
}



