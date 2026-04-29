<?php

class linksManager extends errorLogger
{
    /**
     * Structure links cache: [direction][elementId][type] - this way we minimize its size in memory
     *
     * @var persistableObject[]
     */
    protected $elementsLinks = [];
    /**
     * @var persistableObject[]
     */
    protected $elementsConnectedId = [];
    /**
     * @var persistableCollection
     */
    protected $linksDataCollection;
    protected $db;

    /**
     *
     */
    public function __construct()
    {
        $this->linksDataCollection = persistableCollection::getInstance('structure_links');
        $this->elementsLinks['parent'] = [];
        $this->elementsLinks['child'] = [];
        $this->elementsConnectedId['parent'] = [];
        $this->elementsConnectedId['child'] = [];
    }

    /**
     * loads all non-loaded links for the query parameters, cache the results, compile and return results from cache
     *
     * @param int $elementId
     * @param string[]|string $types - structure link type, optional
     * @param string[]|string|null $elementRoles - role of element ("parent" or "child" or null if both opposite roles are required)
     * @param bool $forceUpdate - if provided, forces data loading from database regardless of cache
     * @return persistableObject[]
     */
    public function getElementsLinks($elementId, $types = 'structure', $elementRoles = null, $forceUpdate = false)
    {
        if (is_null($elementRoles) || $elementRoles == '') {
            $elementRoles = [
                'child',
                'parent',
            ];
        } elseif (!is_array($elementRoles)) {
            $elementRoles = [$elementRoles];
        }

        if ($types && !is_array($types)) {
            $types = [$types];
        }
        if ($forceUpdate || $this->linksLoadRequired($elementId, $types, $elementRoles)) {
            $elementsLinks = $this->loadLinks($elementId, $types, $elementRoles);
            $this->cacheLinks($elementsLinks, $elementId, $types, $elementRoles);
        }
        return $this->compileElementLinksList($elementId, $types, $elementRoles);
    }

    protected function linksLoadRequired($elementId, $types, $elementRoles)
    {
        //check each role separately
        foreach ($elementRoles as $elementRole) {
            //check if at least something was loaded for this role and element id
            if (!isset($this->elementsLinks[$elementRole][$elementId])) {
                return true;
            }

            //we need all types
            if (!$types && !isset($this->elementsLinks[$elementRole][$elementId]['*'])) {
                return true;
            }
            //the most narrow case - id and type are specified
            if ($types) {
                foreach ($types as &$type) {
                    if (!isset($this->elementsLinks[$elementRole][$elementId][$type])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function compileElementLinksList($elementId, $types, $elementRoles)
    {
        $result = [];
        foreach ($elementRoles as $elementRole) {
            if (!$types) {
                $result = array_merge($result, $this->elementsLinks[$elementRole][$elementId]['*']);
            } else {
                foreach ($types as &$type) {
                    $result = array_merge($result, $this->elementsLinks[$elementRole][$elementId][$type]);
                }
            }
        }
        return $result;
    }

    protected function cacheLinks($elementsLinks, $elementId, $type, $elementRoles)
    {
        foreach ($elementRoles as $elementRole) {
            $this->cacheLinksByRole($elementsLinks, $elementId, $type, $elementRole);
        }
    }

    protected function cacheLinksByRole($elementsLinks, $elementId, $types, $elementRole)
    {
        if (!isset($this->elementsLinks[$elementRole][$elementId])) {
            $this->elementsLinks[$elementRole][$elementId] = [];
        }
        if (!$types) {
            if (!isset($this->elementsLinks[$elementRole][$elementId]['*'])) {
                $this->elementsLinks[$elementRole][$elementId]['*'] = [];
            }

            //we don't want to sort links which has already been loaded
            $loadedLinksIndex = $this->elementsLinks[$elementRole][$elementId];

            //now we sort all loaded links according to types, ignoring the links with previously loaded types
            foreach ($elementsLinks as &$link) {
                if (!isset($loadedLinksIndex[$link->type]) && ($elementRole == 'child' && $link->childStructureId == $elementId || $elementRole == 'parent' && $link->parentStructureId == $elementId)) {
                    $this->elementsLinks[$elementRole][$elementId][$link->type][] = $link;
                }
            }
            //then we assemble from all loaded link types the final result.
            //This ensures that previously created non-persisted links get included into array of all elements
            foreach ($this->elementsLinks[$elementRole][$elementId] as $type => &$links) {
                if ($type != '*') {
                    $this->elementsLinks[$elementRole][$elementId]['*'] = array_merge($this->elementsLinks[$elementRole][$elementId]['*'], $links);
                }
            }
        } else {
            foreach ($types as &$type) {
                if (!isset($this->elementsLinks[$elementRole][$elementId][$type])) {
                    $this->elementsLinks[$elementRole][$elementId][$type] = [];
                } else {
                    // maintain pending links
                    foreach ($this->elementsLinks[$elementRole][$elementId][$type] as $key => $link) {
                        if ($link->loaded || $link->persisted) {
                            unset($this->elementsLinks[$elementRole][$elementId][$type][$key]);
                        }
                    }
                }
            }
            foreach ($elementsLinks as &$link) {
                //place appropriate links to appropriate directions/roles
                if ($elementRole === 'child' && $link->childStructureId == $elementId || $elementRole == 'parent' && $link->parentStructureId == $elementId) {
                    $this->elementsLinks[$elementRole][$elementId][$link->type][] = $link;
                }
            }
        }
    }

    protected function loadLinks($elementId, $types, $elementRoles)
    {
        $result = [];
        foreach ($elementRoles as $elementRole) {
            $searchFields = [];
            if ($elementRole === 'parent') {
                $searchFields[] = ['parentStructureId', '=', $elementId];
            } elseif ($elementRole === 'child') {
                $searchFields[] = ['childStructureId', '=', $elementId];
            }
            if ($types) {
                $searchFields[] = ['type', 'in', $types];
            }
            $orderFields = ['position' => 'asc'];
            if (($elementsLinks = $this->linksDataCollection->loadNew($searchFields, $orderFields)) !== false) {
                $result = array_merge($result, $elementsLinks);
            }
        }

        return $result;
    }

    /**
     * Gets link objects indexed by child/parent id number depending on $elementRole
     *
     * @param $elementId
     * @param string $type
     * @param string $elementRole
     * @return persistableObject[]
     */
    public function getElementsLinksIndex($elementId, $type = 'structure', $elementRole = null)
    {
        $linksIndex = [];
        if ($elementLinks = $this->getElementsLinks($elementId, $type, $elementRole)) {
            foreach ($elementLinks as &$link) {
                if ($elementRole == 'child') {
                    $linksIndex[$link->parentStructureId] = $link;
                } else {
                    $linksIndex[$link->childStructureId] = $link;
                }
            }
        }
        return $linksIndex;
    }

    /**
     * @param $elementId
     * @param string $types
     * @param string[]|string|null $sourceElementRoles - : are we looking for child or parent elements?
     * @return mixed
     */
    public function getConnectedIdList(
        $elementId,
        $types = 'structure',
        $sourceElementRoles = null
    )
    {
        if (!is_array($types)) {
            $types = (array)$types;
        }
        if (is_null($sourceElementRoles) || $sourceElementRoles == '') {
            $sourceElementRoles = [
                'child',
                'parent',
            ];
        } elseif (!is_array($sourceElementRoles)) {
            $sourceElementRoles = [$sourceElementRoles];
        }
        $result = [];
        foreach ($sourceElementRoles as $elementRole) {
            foreach ($types as &$type) {
                if (!isset($this->elementsConnectedId[$elementRole][$elementId])) {
                    $this->elementsConnectedId[$elementRole][$elementId] = [];
                }
                if (!isset($this->elementsConnectedId[$elementRole][$elementId][$type])) {
                    $foundIdList = [];
                    if ($elementLinks = $this->getElementsLinks($elementId, $type, $elementRole, false)) {
                        if ($elementRole == 'child') {
                            foreach ($elementLinks as &$link) {
                                if ($link->childStructureId == $elementId) {
                                    $foundIdList[] = $link->parentStructureId;
                                }
                            }
                        } elseif ($elementRole == 'parent') {
                            foreach ($elementLinks as &$link) {
                                if ($link->parentStructureId == $elementId) {
                                    $foundIdList[] = $link->childStructureId;
                                }
                            }
                        }
                    }
                } else {
                    $foundIdList = $this->elementsConnectedId[$elementRole][$elementId][$type];
                }
                $result = array_merge($result, $foundIdList);
            }
        }
        return array_unique($result);
    }

    /**
     * Returns index of all connected id numbers
     *
     * @param $elementId
     * @param string $type
     * @param string $elementRole
     * @return array
     */
    public function getConnectedIdIndex($elementId, $type = 'structure', $elementRole = null)
    {
        $index = [];
        if ($foundIdList = $this->getConnectedIdList($elementId, $type, $elementRole)) {
            $index = array_flip($foundIdList);
        }
        return $index;
    }

    /**
     * @param $parentId
     * @param $childId
     * @param string $linkType
     * @return bool|persistableObject
     */

    /**
     * @param $parentId
     * @param $childId
     * @param string $linkType
     * @param bool $bothDirections
     * @return bool|persistableObject
     */
    public function linkElements($parentId, $childId, $linkType = 'structure', $bothDirections = false)
    {
        $result = false;

        //todo: potential speedup place for large projects. check for existance without getting all links
        $links = $this->getElementsLinks($parentId, $linkType);
        foreach ($links as &$link) {
            if ($link->parentStructureId == $parentId && $link->childStructureId == $childId) {
                $result = $link;
                break;
            }
        }

        if (!$result) {
            if (!$bothDirections) {
                $linksObject = $this->createLinkObject($parentId, $childId, $linkType);
                $linksObject->persist();
            } else {
                $linksObject = $this->createLinkObject($childId, $parentId, $linkType);
                $linksObject->persist();

                $linksObject = $this->createLinkObject($parentId, $childId, $linkType);
                $linksObject->persist();
            }
            $result = $linksObject;

            if (isset($this->elementsConnectedId['child']) && isset($this->elementsConnectedId['child'][$childId]) && isset($this->elementsConnectedId['child'][$childId][$linkType])
            ) {
                $this->elementsConnectedId['child'][$childId][$linkType][] = $parentId;
            }
            if (isset($this->elementsConnectedId['parent']) && isset($this->elementsConnectedId['parent'][$parentId]) && isset($this->elementsConnectedId['parent'][$parentId][$linkType])
            ) {
                $this->elementsConnectedId['parent'][$parentId][$linkType][] = $childId;
            }
        } elseif (!$result->persisted) {
            $result->persist();
        }
        return $result;
    }

    /**
     * @param $parentId
     * @param $childId
     * @param string $linkType
     */
    public function unLinkElements($parentId, $childId, $linkType = 'structure')
    {
        if (is_numeric($childId) && is_numeric($parentId)) {
            $objectsToDelete = [];

            if ($linksList = $this->getElementsLinks($parentId, $linkType, 'parent')) {
                foreach ($linksList as &$link) {
                    if ($link->childStructureId == $childId) {
                        $objectsToDelete[] = $link;
                    }
                }
            }

            if ($linksList = $this->getElementsLinks($parentId, $linkType, 'child')) {
                foreach ($linksList as &$link) {
                    if ($link->parentStructureId == $childId) {
                        $objectsToDelete[] = $link;
                    }
                }
            }
            foreach ($objectsToDelete as &$link) {
                $link->delete();

                if (isset($this->elementsLinks['parent'][$parentId])) {
                    $this->searchAndUnsetLinks($parentId, $childId, $linkType, $this->elementsLinks['parent'][$parentId]);
                    $this->searchAndUnsetLinks($parentId, $childId, '*', $this->elementsLinks['parent'][$parentId]);
                }
                if (isset($this->elementsLinks['child'][$childId])) {
                    $this->searchAndUnsetLinks($parentId, $childId, $linkType, $this->elementsLinks['child'][$childId]);
                    $this->searchAndUnsetLinks($parentId, $childId, '*', $this->elementsLinks['child'][$childId]);
                }
                if (isset($this->elementsConnectedId['child'][$childId])) {
                    unset($this->elementsConnectedId['child'][$childId]);
                }
                if (isset($this->elementsConnectedId['parent'][$parentId])) {
                    unset($this->elementsConnectedId['parent'][$parentId]);
                }
            }
        } else {
            $this->logError('attempt to delete link by non-numeric ID');
        }
    }

    protected function searchAndUnsetLinks($parentId, $childId, $linkType, &$links)
    {
        if (isset($links[$linkType])) {
            foreach ($links[$linkType] as $key => $elementLink) {
                if ($elementLink->parentStructureId == $parentId && $elementLink->childStructureId == $childId) {
                    unset($links[$linkType][$key]);
                }
            }
            if (!$links[$linkType]) {
                unset($links[$linkType]);
            }
        }
    }

    /**
     * Creates link object for provided parent and child ID pair
     *
     * @param int $parentId
     * @param int $childId
     * @param string $linkType - default type is structure
     * @return persistableObject
     */
    public function createLinkObject($parentId, $childId, $linkType = 'structure')
    {
        $newPosition = 10;

        //we ensure to load all links of that type for both parent and child elements
        $this->getElementsLinks($childId, $linkType, 'child');
        if ($olderLinks = $this->getElementsLinks($parentId, $linkType, 'parent')) {
            if ($link = end($olderLinks)) {
                $newPosition = $link->position + 10;
            }
        }

        $linksObject = $this->linksDataCollection->getEmptyObject();
        $linksObject->parentStructureId = $parentId;
        $linksObject->childStructureId = $childId;
        $linksObject->type = $linkType;
        $linksObject->position = $newPosition;
        $this->elementsLinks['parent'][$parentId][$linkType][] = $linksObject;
        $this->elementsLinks['child'][$childId][$linkType][] = $linksObject;
        return $linksObject;
    }

    /**
     * If element's id has been changed then it should be reregistered in all indexes with a new id.
     *
     * @param $originalId
     * @param $newId
     */
    public function reRegisterElement($originalId, $newId)
    {
        if (isset($this->elementsLinks['child'][$originalId]) || isset($this->elementsLinks['parent'][$originalId])) {
            $childId = $parentId = 0;
            $linkType = '';

            if ($oldLinks = $this->getElementsLinks($originalId, null)) {
                foreach ($oldLinks as &$link) {
                    if ($link->childStructureId == $originalId) {
                        $link->childStructureId = $newId;
                        $childId = $newId;
                        $parentId = $link->parentStructureId;
                        $linkType = $link->type;
                    }
                    if ($link->parentStructureId == $originalId) {
                        $link->parentStructureId = $newId;
                        $childId = $link->childStructureId;
                        $parentId = $newId;
                        $linkType = $link->type;
                    }
                }
            }
            if ($childId && $parentId) {
                if (isset($this->elementsConnectedId['child'][$childId]) && isset($this->elementsConnectedId['child'][$childId][$linkType])
                ) {
                    $this->elementsConnectedId['child'][$childId][$linkType][] = $parentId;
                }
                if (isset($this->elementsConnectedId['parent'][$parentId]) && isset($this->elementsConnectedId['parent'][$parentId][$linkType])
                ) {
                    $this->elementsConnectedId['parent'][$parentId][$linkType][] = $childId;
                }
            }
            if (isset($this->elementsLinks['child'][$originalId])) {
                $this->elementsLinks['child'][$newId] =& $this->elementsLinks['child'][$originalId];
                unset($this->elementsLinks['child'][$originalId]);
            }
            if (isset($this->elementsLinks['parent'][$originalId])) {
                $this->elementsLinks['parent'][$newId] =& $this->elementsLinks['parent'][$originalId];
                unset($this->elementsLinks['parent'][$originalId]);
            }
        }
    }

    public function resetElementsCacheById(int|string $id): void
    {
        unset($this->elementsLinks['child'][$id], $this->elementsLinks['parent'][$id]);
    }

    public function getLink($parentElementId, $elementId, $type = 'structure')
    {
        foreach ($this->getElementsLinks($parentElementId, $type, 'parent') as $link) {
            if ($link->childStructureId == $elementId) {
                return $link;
            }
        }

        return false;
    }
}