<?php

use App\Structure\ActionFactory;

class structureManager implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    /**
     * @var array<structureElement|null>
     */
    protected $elementsList = [];
    protected $elementsParents = [];

    protected $elementsDataCollection;

    protected $cachedMarkers = [];

    public $rootElementId;
    protected $rootElementMarker;
    protected $currentElement;
    public $newElementParameters;
    protected $newElementLinkType = 'structure';
    /**
     * @var Cache
     */
    protected $cache;
    protected $cacheLifeTime = 60 * 60 * 3;

    /**
     * @var privilegesManager
     */
    protected $privilegesManager;
    /**
     * @var linksManager
     */
    protected $linksManager;
    /**
     * @var LanguagesManager
     */
    protected $languagesManager;

    protected ActionFactory $actionFactory;
    protected $defaultRoles = [];

    protected $requestedPath = [];
    protected $requestedPathString = '';
    public $customActions = [];
    public $defaultActions = [];
    protected array $pathSearchAllowedLinks;
    protected $privilegeChecking = true;
    protected $deniedCopyLinkTypes = [];
    protected $elementPathRestrictionId;
    protected $shortestChains = [];

    /**
     * @param LanguagesManager $languagesManager
     */
    public function setLanguagesManager($languagesManager): void
    {
        $this->languagesManager = $languagesManager;
    }

    public function setLinksManager(linksManager $linksManager): void
    {
        $this->linksManager = $linksManager;
    }

    public function setPrivilegesManager(privilegesManager $privilegesManager): void
    {
        $this->privilegesManager = $privilegesManager;
    }

    public function setActionFactory(ActionFactory $actionFactory): void
    {
        $this->actionFactory = $actionFactory;
    }

    /**
     * @param Cache $cache
     */
    public function setCache($cache): void
    {
        $this->cache = $cache;
    }

    public function __destruct()
    {
        //        $this->elementsLoadReport();
    }

    public function __construct()
    {
        $this->defaultRoles = [
            'content',
            'container',
            'hybrid',
        ];
        $this->elementsDataCollection = persistableCollection::getInstance('structure_elements');
    }

    /**
     * Returns the 1-dimensional array of complete structureElements tree.
     *
     * @param int $elementId
     * @param int $roles
     * @param string $linkType
     * @param bool $restrictLinkTypes
     * @param structureElement[] $flatTree
     * @param array $usedIds - prevents cycling
     * @return structureElement[]
     */
    public function getElementsFlatTree(
        $elementId,
        $roles = null,
        $linkType = 'structure',
        $restrictLinkTypes = false,
        &$flatTree = [],
        &$usedIds = [],
    ): array
    {
        $treeLevel = $this->getElementsChildren($elementId, $roles, $linkType, null, $restrictLinkTypes);
        foreach ($treeLevel as $element) {
            if (!in_array($element->id, $usedIds)) {
                $usedIds[] = $element->id;
                $flatTree[] = $element;
                $this->getElementsFlatTree($element->id, $roles, $linkType, $restrictLinkTypes, $flatTree, $usedIds);
            }
        }
        return $flatTree;
    }

    /**
     * Returns one-dimensional array of all elements mentioned in structurePath
     * @param string[] $structurePath
     * @param int $parentElementId
     * @param structureElement[] $elementsChain
     * @return structureElement[]
     */
    public function getElementsChain($structurePath = [], $parentElementId = null, &$elementsChain = []): array
    {
        if ($structurePath) {
            if ($parentElementId === null) {
                $parentElementId = $this->getRootElementId();
            }
            //take the first element name from the path array
            $currentStructureName = array_shift($structurePath);

            //search for the element by its structureName within the current parent element's children
            if ($element = $this->getElementByStructureName($currentStructureName, $parentElementId)) {
                $elementsChain[] = $element;
                //make recursive call to getElementsChain() using the found child element as parent
                $this->getElementsChain($structurePath, $element->id, $elementsChain);
            }
        }
        return $elementsChain;
    }

    /**
     * Searches and returns the element defined by it's path
     *
     * @param string[] $structurePath
     * @param int $parentElementId
     */
    public function getElementByPath($structurePath = null, $parentElementId = null): ?structureElement
    {
        if ($structurePath) {
            if ($parentElementId === null) {
                $parentElementId = $this->getRootElementId();
            }

            //take the first element name from the path array
            $currentStructureName = array_shift($structurePath);

            //search for the element by its structureName within the current parent element's children
            if ($element = $this->getElementByStructureName($currentStructureName, $parentElementId)) {
                if ($structurePath) {
                    if ($childElement = $this->getElementByPath($structurePath, $element->id)) {
                        return $childElement;
                    }

                    return null;
                }

                return $this->elementsList[$element->id];
            }

            return null;
        }

        return $this->getRootElement();
    }

    /**
     * Searches for all elements of requested type restricted with the defined parent element if required
     *
     * @param string|string[] $structureType
     * @param int $parentElementId
     * @param string[] $orderFields
     * @param int|int[] $limit
     * @return structureElement[]
     */
    public function getElementsByType($structureType, $parentElementId = null, $orderFields = [], $limit = []): array
    {
        if ($parentElementId === null) {
            $parentElementId = $this->getRootElementId();
        }
        $result = [];

        if ($foundObjects = $this->elementsDataCollection->load(
            ['structureType' => $structureType],
            $orderFields,
            false,
            $limit
        )
        ) {
            foreach ($foundObjects as $dataObject) {
                if ($this->checkElementInParent($dataObject->id, $parentElementId)) {
                    if ($newElement = $this->getElementById($dataObject->id, $parentElementId)) {
                        $result[] = $newElement;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * This method doesn't load new elements, but it can search for the known type within the already loaded elements list
     *
     * @param string $type
     * @return structureElement[]
     */
    public function getLoadedElementsByType($type): array
    {
        $elements = [];
        foreach ($this->elementsList as $element) {
            if ($element && $element->structureType === $type) {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    /**
     * Returns the list of element's parent elements according to provided link type
     *
     * @return structureElement[]
     */
    public function getElementsParents(int|string $elementId, ?string $linkType = null, bool $restrictLinkTypes = true): array
    {
        if (!$linkType) {
            $linkType = '';
        }
        if (isset($this->elementsParents[$elementId][$linkType])) {
            return $this->elementsParents[$elementId][$linkType];
        }
        if ($restrictLinkTypes && !$linkType) {
            $elementsLinks = $this->linksManager->getElementsLinks(
                $elementId,
                $this->getPathSearchAllowedLinks(),
                'child'
            );
        } else {
            $elementsLinks = $this->linksManager->getElementsLinks(
                $elementId,
                $linkType,
                'child'
            );
        }

        foreach ($elementsLinks as $link) {
            if ($element = $this->getElementById($link->parentStructureId)) {
                if (!isset($this->elementsParents[$elementId][$linkType])) {
                    $this->elementsParents[$elementId][$linkType] = [];
                }
                $this->elementsParents[$elementId][$linkType][] = $element;
            }
        }
        return $this->elementsParents[$elementId][$linkType] ?? [];
    }

    /**
     * Returns the first parent of element specified by id
     *
     * @param int $elementId
     * @param null $linkType
     */
    public function getElementsFirstParent($elementId, $linkType = null): ?structureElement
    {
        $result = null;
        if ($parentsList = $this->getElementsParents($elementId, $linkType)) {
            $result = reset($parentsList);
        }
        return $result;
    }

    /**
     * Returns the requested parent of element specified by id
     *
     * @param int $elementId
     * @param null $linkType
     */
    public function getElementsRequestedParent($elementId, $linkType = null): ?structureElement
    {
        if ($parentsList = $this->getElementsParents($elementId, $linkType)) {
            foreach ($parentsList as $parentElement) {
                if ($parentElement->requested) {
                    return $parentElement;
                }
            }
        }
        return null;
    }

    /**
     * Deprecated, use setRequestedPath and getRootElement instead
     *
     * @param string[] $controllerRequestedPath
     *
     * @deprecated
     */
    public function buildRequestedPath($controllerRequestedPath = []): ?structureElement
    {
        $this->logError('deprecated method buildRequestedPath used, use setRequestedPath instead');
        $this->setRequestedPath($controllerRequestedPath);

        $elementId = $this->getRootElementId();

        //check if element is already loaded from the storage
        if (isset($this->elementsList[$elementId]) && is_object($this->elementsList[$elementId])) {
            return $this->elementsList[$elementId];
        }

        //load element from the storage
        if ($element = $this->loadRootElement($elementId)) {
            return $element;
        }
        return null;
    }

    public function setRequestedPath($requestedPath): void
    {
        $this->requestedPath = $requestedPath;
        if ($requestedPath) {
            $this->requestedPathString = implode('/', $requestedPath) . '/';
        } else {
            $this->requestedPathString = '';
        }
    }

    /**
     * Current element is an element which should be displayed for the current requested URL
     *
     * @param string[] $currentElementPath
     */
    public function getCurrentElement($currentElementPath = null): ?structureElement
    {
        if ($this->currentElement === null) {
            if ($currentElementPath === null) {
                $currentElementPath = $this->requestedPath;
            }
            if (isset($this->newElementParameters[$this->requestedPathString])) {
                $this->requestedPathString .= "type:" . $this->newElementParameters[$this->requestedPathString]["type"] . "/";
            }
            //check if there was a new element under the current path and create it
            if ($currentElementPath) {
                $currentElementPathString = implode('/', $currentElementPath) . '/';
            } else {
                $currentElementPathString = '';
            }
            if (isset($this->newElementParameters[$currentElementPathString])) {
                if ($parentElement = $this->getElementByPath($currentElementPath)) {
                    //this check guarantees that new element would be created only once
                    if (isset($this->newElementParameters[$currentElementPathString])) {
                        $newElementAction = $this->newElementParameters[$currentElementPathString]['action'];
                        $newElementType = $this->newElementParameters[$currentElementPathString]['type'];

                        unset($this->newElementParameters[$currentElementPathString]);
                        $this->currentElement = $this->createElement(
                            $newElementType,
                            $newElementAction,
                            $parentElement->id,
                            true
                        );
                    }
                }
            }

            //get current element according to requested path
            if (!$this->currentElement) {
                $this->currentElement = $this->getElementByPath($currentElementPath);
            }
        }

        return $this->currentElement;
    }

    public function setCurrentElement($currentElement): void
    {
        $this->currentElement = $currentElement;
        $currentElement->final = true;
        $currentElement->requested = true;
    }

    /**
     * Loads root element from storage
     *
     * @param int $elementId
     */
    protected function loadRootElement($elementId = null): ?structureElement
    {
        if ($elementId === null) {
            $elementId = $this->getRootElementId();
        }
        $element = null;

        //load element from the storage
        if ($elementsList = $this->loadElementsToParent([$elementId])) {
            $element = array_shift($elementsList);
        }

        return $element;
    }

    /**
     * Preload and return root element
     *
     */
    public function getRootElement(): ?structureElement
    {
        if (isset($this->elementsList[$this->getRootElementId()])) {
            $element = $this->elementsList[$this->getRootElementId()] ?? null;
        } else {
            $element = $this->loadRootElement();
        }

        return $element;
    }

    /**
     * Searches for the element with specified structure name within specified parent element's children. Makes a search in storage and loads the element if required
     *
     * @param string $childElementName
     * @param int $parentElementId
     */
    public function getElementByStructureName($childElementName, $parentElementId): ?structureElement
    {
        $result = null;
        if ($childElementName) {
            if (!isset($this->elementsList[$parentElementId])) {
                $this->elementsList[$parentElementId] = $this->getElementById($parentElementId);
            }
            if (isset($this->elementsList[$parentElementId])) {
                $cacheKey = $parentElementId . ':e:' . 'name' . $childElementName;
                if ($id = $this->cache->get($cacheKey)) {
                    return $this->getElementById($id);
                }

                $parentElement = $this->elementsList[$parentElementId];
                foreach ($parentElement->childrenList as $element) {
                    if ($element->structureName == $childElementName) {
                        $result = $this->elementsList[$element->id];
                        break;
                    }
                }
                if (!$result) {
                    //this shouldn't be switched to 'getConnectedIds' because of speed issues. benchmark first
                    //todo: to join?
                    $connectedLinks = $this->linksManager->getElementsLinks(
                        $parentElementId,
                        $this->getPathSearchAllowedLinks(),
                        'parent'
                    );
                    $connectedIds = [];
                    foreach ($connectedLinks as $link) {
                        $connectedIds[] = $link->childStructureId;
                    }
                    /**
                     * @var \Illuminate\Database\Query\Builder $query
                     */
                    $query = $this->getService('db')->table('structure_elements');
                    $query
                        ->select('id')
                        ->where('structureName', '=', $childElementName)
                        ->whereIn('id', $connectedIds)
                        ->limit(1);

                    if ($record = $query->first()) {
                        $id = $record['id'];
                        $this->loadElementsToParent([$id], $parentElementId);
                        if (isset($this->elementsList[$id])) {
                            $result = $this->elementsList[$id];
                        }
                    }
                }
                if (!$result) {
                    $this->getElementsChildren($parentElement->id);
                    foreach ($parentElement->childrenList as $element) {
                        if ($element->structureName == $childElementName) {
                            $result = $this->elementsList[$element->id];
                        }
                    }
                }
                if ($result) {
                    $this->cache->set($cacheKey, $result->id, 600);
                    $this->registerElementCacheKey($result->id, $cacheKey);
                }
            }
        }

        return $result;
    }

    /**
     * Sets the new parent element for specified element
     *
     * @param int $sourceParentId
     * @param int $targetId
     * @param int $elementId
     * @param string $linkType
     */
    public function moveElement($sourceParentId, $targetId, $elementId, $linkType = null): void
    {
        $elementLinks = $this->linksManager->getElementsLinks($elementId, '', 'child');
        foreach ($elementLinks as &$link) {
            if ($link->parentStructureId == $sourceParentId && ($linkType === null || $linkType == $link->type)) {
                $link->parentStructureId = $targetId;
                $link->persist();
            }
        }
        $parentLinks = $this->linksManager->getElementsLinks($elementId, 'parent');
        foreach ($parentLinks as &$link) {
            if ($link->parentStructureId == $sourceParentId && ($linkType === null || $linkType == $link->type)) {
                $link->parentStructureId = $targetId;
                $link->persist();
            }
        }
    }

    /**
     * Copies a list of elements into a new parent element
     *
     * @param int[] $idList - list of id numbers of elements to copy
     * @param int $targetId - new parent element id
     * @param string[] $linkTypes - link types of copied connected elements
     * @param null $parentLinkType - all copied top-elements will use this to link with new parents
     * @return array $copiesInformation
     */
    public function copyElements($idList, $targetId, $linkTypes = null): array
    {
        //by default copy only elements via "structure" links
        if ($linkTypes === null) {
            $linkTypes = ['structure'];
        }

        //copy all the elements including their structure children tree
        $copiesInformation = [];
        foreach ($idList as $sourceId) {
            $parentLinkType = false;
            if ($elementLinks = $this->linksManager->getElementsLinks($sourceId, null, 'child')) {
                foreach ($elementLinks as $elementLink) {
                    if (in_array($elementLink->type, $linkTypes)) {
                        $parentLinkType = $elementLink->type;
                        break;
                    }
                }

                if ($parentLinkType) {
                    $this->copyElement($sourceId, $targetId, $parentLinkType, $linkTypes, $copiesInformation);
                }
            }
        }

        //replicate all copied elements links using the information from old elements to maintain same connections
        foreach ($copiesInformation as $sourceId => &$newId) {
            if ($elementLinks = $this->linksManager->getElementsLinks($sourceId, null)) {
                foreach ($elementLinks as &$link) {
                    if (!in_array($link->type, $linkTypes) && !in_array($link->type, $this->deniedCopyLinkTypes)) {
                        if ($link->parentStructureId == $sourceId) {
                            $connectedId = $link->childStructureId;
                            if (isset($copiesInformation[$connectedId])) {
                                $connectedId = $copiesInformation[$connectedId];
                            }
                            $this->linksManager->linkElements($newId, $connectedId, $link->type);
                        } elseif ($link->childStructureId == $sourceId) {
                            $connectedId = $link->parentStructureId;
                            if (isset($copiesInformation[$connectedId])) {
                                $connectedId = $copiesInformation[$connectedId];
                            }
                            $this->linksManager->linkElements($connectedId, $newId, $link->type);
                        }
                    }
                }
            }
        }
        return $copiesInformation;
    }

    /**
     * makes a copy of one element and returns it
     *
     * @param int $sourceId - source element id
     * @param int $targetId - id number of new parent element
     * @param $currentLinkType - link type of newly created element to connect with previous iteration parent
     * @param string[] $linkTypes - link types of copied connected elements
     * @param int[] $copiesInformation - index of old element relations to newly created element ID relations, filled automatically
     */
    protected function copyElement($sourceId, $targetId, $currentLinkType, $linkTypes, &$copiesInformation = []): ?structureElement
    {
        $newElement = null;
        if ($sourceElement = $this->getElementById($sourceId)) {
            $sourceStructureData = $sourceElement->getStructureData();
            //copy of product shouldn't contain creation/modification dates of original
            unset($sourceStructureData['dateCreated']);
            unset($sourceStructureData['dateModified']);
            $sourceModuleData = $sourceElement->getModuleData();

            $structureDataObject = $this->elementsDataCollection->getEmptyObject();
            $structureDataObject->setData($sourceStructureData);
            $structureDataObject->dateCreated = time();
            $structureDataObject->persist();

            $moduleResourceName = $sourceElement->getDataResourceName();

            foreach ($sourceModuleData as $languageId => &$languageData) {
                $collection = persistableCollection::getInstance($moduleResourceName);
                $moduleDataObject = $collection->getEmptyObject();
                $moduleDataObject->setData($languageData);
                $moduleDataObject->id = $structureDataObject->id;
                $moduleDataObject->languageId = $languageId;
                $moduleDataObject->persist();
            }
            $this->linksManager->createLinkObject($targetId, $structureDataObject->id, $currentLinkType);

            if ($newElement = $this->manufactureElement($structureDataObject, $targetId)) {
                $newElement->prepareActualData();
                $newElement->structureName = $newElement->getTitle();
                $newElement->copyExtraData($sourceElement->id);
                $newElement->persistElementData();

                $copiesInformation[$sourceId] = $newElement->id;
                foreach ($linkTypes as $linkType) {
                    if ($childrenList = $this->getElementsChildren($sourceElement->id, null, $linkType)) {
                        foreach ($childrenList as $childElement) {
                            $this->copyElement(
                                $childElement->id,
                                $newElement->id,
                                $linkType,
                                $linkTypes,
                                $copiesInformation
                            );
                        }
                    }
                }

                $this->performAction($newElement);
            }
        }
        return $newElement;
    }

    public function moveElements($idList, $targetId, $linkTypes = null): true
    {
        if ($linkTypes === null) {
            $linkTypes = ['structure'];
        }
        foreach ($idList as $elementId) {
            foreach ($linkTypes as &$linkType) {
                if ($parentIdList = $this->linksManager->getConnectedIdList($elementId, $linkType, 'child')) {
                    foreach ($parentIdList as &$parentId) {
                        $this->linksManager->unLinkElements($parentId, $elementId, $linkType);
                    }
                    $this->linksManager->linkElements($targetId, $elementId, $linkType);
                }
            }
        }
        return true;
    }

    /**
     * Changes default link type for all created new elements.
     *
     * @param string $linkType
     */
    public function setNewElementLinkType($linkType = null): void
    {
        if (!$linkType) {
            $linkType = 'structure';
        }
        $this->newElementLinkType = $linkType;
    }

    /**
     * Error logging
     *
     * @param string $text
     */
    public function logError($text): void
    {
        $errorLog = ErrorLog::getInstance();
        $errorLog->logMessage('structureManager', $text);
    }

    /**
     * Creates an empty structure element with empty data
     *
     * @param string $type Element type (structureType)
     * @param string $action Initial action to perform
     * @param int|string|null $parentElementId Parent element ID or marker
     * @param bool $setCurrent Whether to set this element as current in structure manager
     * @param string|null $linkType Link type to use for connection. If null, default new element link type (usually 'structure') is used.
     * @return structureElement|null
     */
    public function createElement(
        string          $type,
        string          $action,
        int|string|null $parentElementId = null,
        bool            $setCurrent = false,
        ?string         $linkType = null,
    ): ?structureElement
    {
        if ($parentElementId === null) {
            $parentElementId = $this->getRootElementId();
        }
        $id = '';
        if ($parentElementId === 0) {
            $id = 'type:' . $type . '/action:' . $action;
        } elseif ($parentElement = $this->getElementById($parentElementId)) {
            $id = $parentElement->structurePath . 'type:' . $type . '/action:' . $action;
        }

        if ($id && !isset($this->elementsList[$id])) {
            $this->customActions[$id] = $action;

            $dataObject = $this->elementsDataCollection->getEmptyObject();
            $dataObject->id = $id;
            $dataObject->structureType = $type;
            $dataObject->structureName = '';

            //create temporary link object which will be automatically saved afterwards
            if ($parentElementId != 0) {
                $newElementLinkType = $linkType ?? $this->newElementLinkType;

                $this->linksManager->createLinkObject($parentElementId, $id, $newElementLinkType);
            }
            if ($newElement = $this->manufactureElement($dataObject, $parentElementId)) {
                $newElement->newlyCreated = true;
                $newElement->createEmptyModuleObjects();
                if ($setCurrent) {
                    $this->setCurrentElement($newElement);
                }
                $this->performAction($newElement);
            }
            return $newElement;
        }

        return null;
    }

    protected function getRequestedRoles($allowedRoles): array
    {
        if ($allowedRoles === null) {
            $requestedRoles = $this->defaultRoles;
        } else {
            if ($allowedRoles === 'content' || $allowedRoles === 'container') {
                $requestedRoles = [
                    $allowedRoles,
                    'hybrid',
                ];
            } elseif (!is_array($allowedRoles)) {
                $requestedRoles = [$allowedRoles];
            } else {
                $requestedRoles = $allowedRoles;
            }
        }
        return $requestedRoles;
    }

    /**
     * @param int $parentElementId
     * @param string[]|null $allowedRoles
     * @param string|string[] $linkTypes
     * @param string|string[]|null $allowedTypes
     * @param bool $restrictLinkTypes
     * @return structureElement[]
     */
    public function getElementsChildren(
        $parentElementId,
        $allowedRoles = null,
        $linkTypes = 'structure',
        $allowedTypes = null,
        $restrictLinkTypes = false,
    ): array
    {
        $returnList = [];
        if ($parentElement = $this->getElementById($parentElementId)) {
            $requestedRoles = $this->getRequestedRoles($allowedRoles);

            $rolesToLoad = $requestedRoles;
            //check if all required types of children elements are already loaded for this structure element
            foreach ($rolesToLoad as $key => $role) {
                if ($parentElement->getChildrenLoadedStatus($linkTypes, $role)) {
                    unset($rolesToLoad[$key]);
                }
            }
            //get all structure links for this element
            if ($restrictLinkTypes) {
                if (!$linkTypes) {
                    $linkTypes = $this->getPathSearchAllowedLinks();
                }
                $elementsLinks = $this->linksManager->getElementsLinks($parentElementId, $linkTypes, 'parent');
            } else {
                $elementsLinks = $this->linksManager->getElementsLinks($parentElementId, $linkTypes, 'parent');
            }


            //make an array of children elements' structure ids
            $idListToLoad = [];
            $idListToReturn = [];
            foreach ($elementsLinks as $elementsLink) {
                $childId = $elementsLink->childStructureId;
                $idListToReturn[] = $childId;
                if (!isset($this->elementsList[$childId]) || !is_object($this->elementsList[$childId])) {
                    $idListToLoad[] = $childId;
                }
            }

            if ($idListToLoad) {
                if ($this->privilegeChecking) {
                    //calculate required privileges for a postcheck
                    $this->privilegesManager->getAllowedElements($parentElementId, $idListToLoad);
                }

                if ($allowedTypes !== null) {
                    $allowedElements = $allowedTypes;
                } else {
                    $allowedElements = [];
                }

                foreach ($rolesToLoad as $role) {
                    $parentElement->setChildrenLoadedStatus($linkTypes, $role, true);
                }

                //load the children elements from the storage and return them
                $this->loadElementsToParent($idListToLoad, $parentElementId, $allowedElements, $rolesToLoad);
            }

            foreach ($idListToReturn as $childElementId) {
                if (
                    isset($this->elementsList[$childElementId]) &&
                    ($childElement = $this->elementsList[$childElementId]) &&
                    in_array($childElement->structureRole, $requestedRoles)
                ) {
                    if (method_exists($childElement, 'getReplacementElements') &&
                        (is_array($replacementElements = $childElement->getReplacementElements($allowedRoles)))
                    ) {
                        //required for product catalogue-like elements
                        $returnList = array_merge($returnList, $replacementElements);
                    } else {
                        $returnList[] = $childElement;
                    }
                }
            }
        }
        return $returnList;
    }

    public function performAction(structureElement $element): void
    {
        if ($this->checkPrivileges($element->id, $element->actionName, $element->structureType)) {
            $element->executeAction();
        } else {
            $this->logError('Insufficient privileges: element ID:' . $element->id . ' action:' . $element->actionName);
        }
    }

    public function checkPrivileges($id, $actionName, $structureType): bool
    {
        $actionObject = $this->actionFactory->makeActionObject($structureType, $actionName);
        if (!$actionObject) {
            return false;
        }
        if (!$this->privilegeChecking || $this->privilegesManager->checkPrivilegesForAction(
                $id,
                $actionObject->getPrivilegeName(),
                $structureType
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param array $idList
     * @param int $parentElementId
     * @param array $allowedElements
     * @param array $allowedRoles
     */
    protected function loadElementsToParent(
        $idList = [],
        $parentElementId = 0,
        $allowedElements = [],
        $allowedRoles = [],
    ): array
    {
        if (!$parentElementId) {
            $parentElementId = $this->getRootElementId();
        }
        $loadedElements = [];
        foreach ($idList as $key => $id) {
            if (isset($this->elementsList[$id]) && ($element = $this->elementsList[$id])) {
                $loadedElements[$key] = $element;
                unset($idList[$key]);
            } elseif ($element = $this->loadFromCache($id, $parentElementId)) {
                $loadedElements[$key] = $element;
                unset($idList[$key]);
            }
        }
        $searchFields = [];
        if ($idList) {
            $searchFields['id'] = $idList;
        } else {
            return $loadedElements;
        }

        if ($allowedElements) {
            $searchFields['structureType'] = $allowedElements;
        }
        //We have only 3 roles: content, container and hybrid.
        //If there are all roles required, then we don't need to restrict this column,
        //so we can save some sql resources by not sending this info
        if ($allowedRoles && count($allowedRoles) < 3) {
            $searchFields['structureRole'] = $allowedRoles;
        }

        //load elements from storage
        $loadedModuleTables = [];
        if ($dataObjects = $this->elementsDataCollection->load($searchFields, ['id' => $idList])) {
            foreach ($dataObjects as $dataObject) {
                $elementId = $dataObject->id;
                if ($loadedElement = $this->manufactureElement($dataObject, $parentElementId)) {
                    $this->createElementCache($elementId);

                    $loadedElements[$elementId] = $this->elementsList[$elementId] ?? null;

                    $loadedModuleTables[$loadedElement->dataResourceName]['language'] = $loadedElement->getCurrentLanguage();
                    $loadedModuleTables[$loadedElement->dataResourceName]['id'][] = $loadedElement->id;
                }
            }

            //preload all module data for all loaded elements - this is faster and more effective than lazy-loading
            if (count($idList) > 1) { // speed gain improbable if only one element is needed
                foreach ($loadedModuleTables as $resourceName => $elementsInfo) {
                    if ($elementsInfo['language'] == 0) {
                        if ($rows = persistableCollection::getInstance($resourceName)
                            ->load(['id' => $elementsInfo['id']])
                        ) {
                            foreach ($rows as $object) {
                                $loadedElements[$object->id]->setModuleDataObject($object, $elementsInfo['language']);
                            }
                        }
                    } else {
                        if ($rows = persistableCollection::getInstance($resourceName)->load(
                            [
                                'id' => $elementsInfo['id'],
                                'languageId' => $elementsInfo['language'],
                            ]
                        )
                        ) {
                            foreach ($rows as $object) {
                                $loadedElements[$object->id]->setModuleDataObject($object, $elementsInfo['language']);
                            }
                        }
                    }
                }
            }

            if (isset($this->elementsList[$parentElementId]) && ($parentObject = $this->elementsList[$parentElementId])) {
                foreach ($idList as $positionItem) {
                    if (isset($loadedElements[$positionItem])) {
                        $parentObject->childrenList[] = $this->elementsList[$positionItem];
                    }
                }
            }

            foreach ($loadedElements as $element) {
                $this->performAction($element);
            }
        }

        return $loadedElements;
    }

    /**
     * Creates empty non-initialized structure element object for provided type
     */
    protected function getElementInstance($type): ?structureElement
    {
        $newElement = null;
        $className = $type . 'Element';
        if (class_exists($className, true)) {
            $newElement = new $className($this->getService(ConfigManager::class)->get('main.rootMarkerPublic'));
            if ($newElement instanceof DependencyInjectionContextInterface) {
                $this->instantiateContext($newElement);
            }
        } else {
            $this->logError('Class "' . $className . '" is missing');
        }
        return $newElement;
    }

    /**
     * @param int $parentId
     */
    protected function manufactureElement(persistableObject $structureObject, $parentId): ?structureElement
    {
        $id = $structureObject->id;
        if (isset($this->elementsList[$id]) && is_object($this->elementsList[$id])) {
            return $this->elementsList[$id];
        }

        $type = $structureObject->structureType;

        if ($manufacturedElement = $this->manufactureElementsObject($id, $type, $parentId)) {
            $manufacturedElement->setStructureDataObject($structureObject);
            $this->generateStructureInfo($manufacturedElement, $parentId);
            if (strtolower($this->requestedPathString) === strtolower($manufacturedElement->structurePath)) {
                $manufacturedElement->final = true;
            }
            $this->elementsList[$manufacturedElement->id] = $manufacturedElement;

            return $this->elementsList[$manufacturedElement->id];
        }

        $this->elementsList[$id] = null;
        return null;
    }

    protected function manufactureElementsObject($id, $type, $parentElementId): ?structureElement
    {
        //todo: update and simplify privileges check
        if ($this->privilegeChecking) {
            $elementPrivileges = $this->privilegesManager->compileElementPrivileges($id, $parentElementId);
        }
        $result = null;
        if (!$this->privilegeChecking || isset($elementPrivileges[$type])) {
            if ($newElement = $this->getElementInstance($type)) {
                if (isset($this->elementsList[$parentElementId])) {
                    $newElement->setCurrentParentElementId($parentElementId);
                }
                $newElement->actionName = $this->defineElementAction($id, $type, $newElement->defaultActionName);
                if ($this->checkPrivileges($id, $newElement->actionName, $type)) {
                    $result = $newElement;
                }
            }
        }
        return $result;
    }

    protected function defineElementAction($id, $type, $defaultAction): ?string
    {
        $actionName = null;
        if (isset($this->customActions[$id])) {
            $actionName = $this->customActions[$id];
        } elseif (isset($this->defaultActions[$type])) {
            $actionName = $this->defaultActions[$type];
        } else {
            $actionName = $defaultAction;
        }
        return $actionName;
    }

    /**
     * @param $element
     */
    public function regenerateStructureInfo($element): void
    {
        if ($element->id != $this->getRootElementId()) {
            if ($parentElements = $this->getElementsParents($element->id)) {
                $currentParent = false;
                foreach ($parentElements as $parentElement) {
                    if ($parentElement->requested) {
                        $currentParent = $parentElement;
                        break;
                    }
                }
                if (!$currentParent) {
                    $currentParent = reset($parentElements);
                }
                if ($currentParent) {
                    $this->generateStructureInfo($element, $currentParent->id);
                }
            } else {
                $this->generateStructureInfo($element);
            }
        }
    }

    /**
     * @param structureElement $element
     */
    protected function generateStructureInfo($element, $parentElementId = false): void
    {
        if ($parentElementId && isset($this->elementsList[$parentElementId])) {
            if (!$element->hasActualStructureInfo() && $element->structureName == '') {
                $element->structurePath = $this->elementsList[$parentElementId]->structurePath . 'type:' . $element->structureType . '/';
                $element->URL = $this->elementsList[$parentElementId]->URL . 'type:' . $element->structureType . '/';
                if ($parentElementId == $this->getRootElementId() || strpos(
                        $this->requestedPathString,
                        $this->elementsList[$parentElementId]->structurePath
                    ) === 0
                ) {
                    $element->requested = true;
                }
            } else {
                $element->structurePath = $this->elementsList[$parentElementId]->structurePath . $element->structureName . '/';
                $element->URL = $this->elementsList[$parentElementId]->URL . $element->structureName . '/';
            }
        } else {
            $element->structurePath = '';
            $element->URL = $this->getRootURL();
        }

        if ($element->structurePath != "") {
            $element->level = $this->elementsList[$parentElementId]->level + 1;
            if (strpos($this->requestedPathString, $element->structurePath) === 0) {
                $element->requested = true;
            }
        } else {
            $element->level = 0;
            $element->requested = true;
        }
        if (strtolower($this->requestedPathString) == strtolower($element->structurePath)) {
            $element->final = true;
        }
    }

    /**
     * This method updates all indexed information about newly created and then persisted element.
     *
     * @param string $originalId - temporary ID in string form (like 'type:element/action:actionName')
     * @param int $newId - new ID from a database
     */
    public function reRegisterElement($originalId, $newId): void
    {
        if (isset($this->elementsParents[$originalId])) {
            $this->elementsParents[$newId] = $this->elementsParents[$originalId];
            unset($this->elementsParents[$originalId]);
        }
        if (isset($this->elementsList[$originalId])) {
            $this->elementsList[$newId] = $this->elementsList[$originalId];
            unset($this->elementsList[$originalId]);
        }

        $this->privilegesManager->reRegisterElement($originalId, $newId);
        $this->linksManager->reRegisterElement($originalId, $newId);
        if ($parentElements = $this->getElementsParents($newId)) {
            foreach ($parentElements as $parentElement) {
                if (isset($this->elementsList[$newId])) {
                    $parentElement->childrenList[] = $this->elementsList[$newId];
                }
            }
        }
    }

    /**
     * Searches and returns first structure element with assigned marker
     *
     * @param string $marker - element's marker to search for
     * @param ?int|null $parentElementId - restriction by parent id
     */
    public function getElementByMarker(string $marker, ?int $parentElementId = null): ?structureElement
    {
        $cacheParentElementId = $parentElementId ?? $this->getRootElementId();

        if (!array_key_exists($marker, $this->cachedMarkers[$cacheParentElementId] ?? [])) {
            $searchFields = ['marker' => $marker];
            $dataCollection = $this->elementsDataCollection->load($searchFields);
            foreach ($dataCollection as $dataElement) {
                if (!$parentElementId || $this->checkElementInParent($dataElement->id, $parentElementId)) {
                    $this->cachedMarkers[$cacheParentElementId][$marker] = $this->getElementById(
                        $dataElement->id,
                        $parentElementId
                    );
                    break;
                }
            }
        }
        return $this->cachedMarkers[$cacheParentElementId][$marker] ?? null;
    }

    public function checkElementInParent($id, $parentId)
    {
        $parentFound = false;
        if ($id == $parentId) {
            $parentFound = true;
        } else {
            if ($dataCollection = $this->linksManager->getElementsLinks(
                $id,
                $this->getPathSearchAllowedLinks(),
                'child',
                false
            )) {
                foreach ($dataCollection as $dataObject) {
                    if ($dataObject->parentStructureId == $parentId) {
                        $parentFound = true;
                        break;
                    }
                }

                if (!$parentFound) {
                    foreach ($dataCollection as $dataObject) {
                        if ($parentFound = $this->checkElementInParent($dataObject->parentStructureId, $parentId)) {
                            break;
                        }
                    }
                }
            }
        }
        return $parentFound;
    }

    /**
     * @param int $id
     * @param int|null $parentId
     */
    public function getElementById($id, $parentId = null, bool $directlyToParent = false): ?structureElement
    {
        if ($id) {
            if (isset($this->elementsList[$id])) {
                return $this->elementsList[$id];
            }
            if ($directlyToParent) {
                if (!$parentId) {
                    $rootId = $this->getRootElementId();
                    if ($rootId && !isset($this->elementsList[$rootId])) {
                        $this->loadRootElement($rootId);
                    }
                }
                $this->loadElementsToParent([$id], $parentId);
            } else {
                $this->loadFromShortestPath($id, $parentId);
            }
            if (!empty($this->elementsList[$id])) {
                return $this->elementsList[$id];
            }
        }
        return null;
    }

    protected function loadFromCache($id, $parentElementId): ?structureElement
    {
        /**
         * @var structureElement $element
         */
        if ($element = $this->cache->get($id . ':e')) {
            if ($element instanceof DependencyInjectionContextInterface) {
                $this->instantiateContext($element);
            }

            if ($this->privilegeChecking) {
                $elementPrivileges = $this->privilegesManager->compileElementPrivileges($id, $parentElementId);
            }
            $type = $element->structureType;
            if (!$this->privilegeChecking || isset($elementPrivileges[$type])) {
                if (isset($this->elementsList[$parentElementId])) {
                    $element->setCurrentParentElementId($parentElementId);
                }
                $element->actionName = $this->defineElementAction($id, $type, $element->defaultActionName);
                if ($this->checkPrivileges($id, $element->actionName, $type)) {
                    $this->generateStructureInfo($element, $parentElementId);
                    $this->elementsList[$element->id] = $element;

                    $this->performAction($element);
                    return $element;
                }
            }
        }
        return null;
    }

    /**
     * @param int|null $parentId
     */
    protected function loadFromShortestPath($id, $parentId = null): void
    {
        if ($id == $this->getRootElementId()) {
            $this->loadRootElement($id);
        }
        if (!$parentId) {
            $parentId = $this->elementPathRestrictionId;
        }
        if ($shortestChain = $this->findShortestParentsChain($id, $parentId)) {
            $parentId = end($shortestChain);
            while ($id = prev($shortestChain)) {
                //if parent element was never loaded let's ensure it's loaded now
                if (!isset($this->elementsList[$parentId])) {
                    $this->getElementById($parentId);
                }
                if (isset($this->elementsList[$parentId])) {
                    if (!isset($this->elementsList[$id])) {
                        if ($this->loadElementsToParent([$id], $parentId)) {
                            $parentId = $id;
                        } else {
                            break;
                        }
                    } else {
                        //if there were not enough privileges, then $this->elementsList[$id]==false
                        if ($this->elementsList[$id]) {
                            $parentId = $id;
                        } else {
                            break;
                        }
                    }
                } else {
                    $this->logError(
                        'ensureElementAvailability unpredicted problem. id:' . $id . ' parentId:' . $parentId
                    );
                }
            }
        }
    }

    public function findPath(int $elementId, int $parentId): ?array
    {
        $shortestChain = $this->findShortestParentsChain($elementId, $parentId);
        if (!$shortestChain) {
            return null;
        }
        $shortestChain = array_reverse($shortestChain);
        array_shift($shortestChain);

        $db = $this->getService('db');
        $records = $db->table('structure_elements')
            ->select(['id', 'structureName'])
            ->whereIn('id', $shortestChain)
            ->get();
        $map = [];
        foreach ($records as $record) {
            $map[$record['id']] = $record['structureName'];
        }

        $path = [];
        foreach ($shortestChain as $item) {
            $path[] = $map[$item] ?? null;
        }
        return $path;
    }

    /**
     * This is recursive method to calculate the quickest/shortest way to load element within it's possible parent chains
     *
     * @param $id - target element id or current recursion level element id
     * @param null $withinParentId - if some parent id should strictly be in the chain, then it can be restricted with this parameter
     * @param int $points - current chain points: the smaller value == the shorter
     * @param array $chainElements - chain elements holder
     */
    protected function findShortestParentsChain(
        $id,
        $withinParentId = null,
        &$points = 0,
        $chainElements = [],
    ): bool|array
    {
        //if we are searching parent within itself then we will get nothing. we should not restrict parent within itself
        if ($withinParentId == $id) {
            $withinParentId = null;
        }
        //in case we don't have root element loaded we should check it as well
        if ($id == $this->rootElementId) {
            return [$id];
        }

        $key = 'ch:' . $this->languagesManager->getCurrentLanguageId() . ':p' . $withinParentId;
        if ($cachedChain = $this->cache->get($id . ":" . $key)) {
            return $cachedChain;
        }
        $withinParentIdKey = $withinParentId ?? '';
        if (isset($this->shortestChains[$id][$withinParentIdKey])) {
            return $this->shortestChains[$id][$withinParentIdKey];
        }
        $this->shortestChains[$id][$withinParentIdKey] = false;
        $shortestChainPointer = &$this->shortestChains[$id][$withinParentIdKey];
        $chainElements[$id] = true;

        if ($parentLinks = $this->linksManager->getElementsLinks($id, $this->getPathSearchAllowedLinks(), 'child')) {
            $parentIds = [];
            foreach ($parentLinks as $parentLink) {
                $parentIds[] = $parentLink->parentStructureId;
            }
            //check all parent routes
            $bestPoints = false;
            foreach ($parentIds as $parentId) {
                if (!isset($chainElements[$parentId])) {
                    $newPoints = $points;
                    if ($withinParentId != $parentIds) {
                        if (!empty($this->elementsList[$parentId])) {
                            if (!$this->elementsList[$parentId]->requested) {
                                $newPoints += 2;
                            } else {
                                $newPoints += 1;
                            }
                        } else {
                            $newPoints += 3;
                        }
                    }
                    if ($chain = $this->findShortestParentsChain(
                        $parentId,
                        $withinParentId,
                        $newPoints,
                        $chainElements
                    )
                    ) {
                        if ($newPoints < $bestPoints || ($bestPoints === false)) {
                            if (!$withinParentId || in_array($withinParentId, $chain)) {
                                $bestPoints = $newPoints;
                                $shortestChainPointer = $chain;
                            }
                        }
                    }
                }
                if ($shortestChainPointer) {
                    $points = $bestPoints;
                    if (reset($shortestChainPointer) != $id) {
                        array_unshift($shortestChainPointer, $id);
                    }
                }
            }
        }
        $this->setElementCacheKey($id, $key, $shortestChainPointer, $this->cacheLifeTime * 2);
        return $shortestChainPointer;
    }

    /**
     * @param bool $parentElementId
     * @param bool $directlyToParent
     * @return structureElement[]
     */
    public function getElementsByIdList($idList, $parentElementId = false, $directlyToParent = false): array
    {
        $elementsList = [];
        if ($idList) {
            if (!$parentElementId) {
                $parentElementId = $this->getRootElementId();
            }
            if ($directlyToParent) {
                if ($allowedElements = $this->privilegesManager->getAllowedElements($parentElementId, $idList)) {
                    // load the children elements from the storage and return them
                    $elementsList = $this->loadElementsToParent($idList, $parentElementId, $allowedElements);
                }
            } else {
                foreach ($idList as $id) {
                    if ($element = $this->getElementById($id, $parentElementId)) {
                        $elementsList[] = $element;
                    }
                }
            }
        }

        return $elementsList;
    }

    public function checkStructureName($element): string
    {
        $elementId = $element->id;
        $currentName = trim($element->structureName) ?: $element->structureType . $elementId;
        $allowedTypes = $this->getPathSearchAllowedLinks();
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');

        $db->statement('DROP TEMPORARY TABLE IF EXISTS temp_structure_links');

        $parentIdsQuery = $db->table('structure_links')
            ->select('parentStructureId')
            ->where('childStructureId', '=', $elementId)
            ->whereIn('type', $allowedTypes);

        $db->statement('CREATE TEMPORARY TABLE temp_structure_links AS ' . $parentIdsQuery->toSql(), $parentIdsQuery->getBindings());

        $subQuery = $db->table('structure_links')
            ->select('childStructureId')
            ->where('childStructureId', '!=', $elementId)
            ->whereIn('type', $allowedTypes)
            ->whereIn('parentStructureId', function ($query) use ($db) {
                $query->select('parentStructureId')->from($db->raw('temp_structure_links'));
            });

        $usedNames = $db->table('structure_elements')
            ->select('structureName')
            ->where('structureName', 'like', $currentName . '%')
            ->whereIn('id', $subQuery)
            ->pluck('structureName');

        $usedNames = array_map('mb_strtolower', $usedNames);

        $newName = $currentName;
        $currentNumber = 1;
        while (in_array(mb_strtolower($newName), $usedNames, true)) {
            $newName = $currentName . $currentNumber;
            $currentNumber++;
        }

        $db->statement('DROP TEMPORARY TABLE IF EXISTS temp_structure_links');

        return $newName;
    }

    /**
     * Returns the ID number of first element with assigned marker.
     * Doesn't check the user privileges or manufacture the object itself, only queries the number in database
     *
     * @param string $marker
     */
    public function getElementIdByMarker($marker): ?int
    {
        $elementId = null;
        $collection = persistableCollection::getInstance('structure_elements');

        $columns = ['id'];

        $conditions = [];
        $conditions[] = [
            'column' => 'marker',
            'action' => '=',
            'argument' => $marker,
        ];

        $result = $collection->conditionalLoad($columns, $conditions, [], 1);
        foreach ($result as &$row) {
            $elementId = $row['id'];
            break;
        }
        return $elementId;
    }

    /**
     * @return mixed
     */
    public function getPathSearchAllowedLinks(): mixed
    {
        return $this->pathSearchAllowedLinks;
    }

    public function setPathSearchAllowedLinks(array $pathSearchAllowedLinks): void
    {
        $this->pathSearchAllowedLinks = $pathSearchAllowedLinks;
    }

    private function getRootUrl()
    {
        return controller::getInstance()->rootURL;
    }

    public function setRootElementId($rootElementId): void
    {
        $this->rootElementId = $rootElementId;
        $this->rootElementMarker = null;
    }

    public function getRootElementId(): int
    {
        if ($this->rootElementId === null) {
            if ($this->rootElementMarker !== null) {
                $this->rootElementId = $this->getElementIdByMarker($this->rootElementMarker);
            }
        }
        return $this->rootElementId;
    }

    /**
     * @return mixed
     */
    public function getRootElementMarker(): mixed
    {
        return $this->rootElementMarker;
    }

    /**
     * @param mixed $rootElementMarker
     */
    public function setRootElementMarker($rootElementMarker): void
    {
        $this->rootElementMarker = $rootElementMarker;
        $this->rootElementId = null;
    }

    public function setPrivilegeChecking($enabled): void
    {
        $this->privilegeChecking = $enabled;
    }

    public function getPrivilegeChecking()
    {
        return $this->privilegeChecking;
    }

    public function getDeniedCopyLinkTypes()
    {
        return $this->deniedCopyLinkTypes;
    }

    public function setDeniedCopyLinkTypes($deniedCopyLinkTypes): void
    {
        $this->deniedCopyLinkTypes = (array)$deniedCopyLinkTypes;
    }

    public function setElementPathRestrictionId($id): void
    {
        $this->elementPathRestrictionId = $id;
    }

    protected function setElementCacheKey($id, $key, $value, $lifeTime): void
    {
        $this->cache->set($id . ':' . $key, $value, $lifeTime);
        $this->registerElementCacheKey($id, $id . ':' . $key);
    }

    protected function registerElementCacheKey($id, $key): void
    {
        if (!($keys = $this->cache->get($id . ':k'))) {
            $keys = [];
        }
        $keys[$key] = 1;
        $this->cache->set($id . ':k', $keys, 3600 * 24 * 7);
    }

    public function clearElementCache($id): void
    {
        if ($keys = $this->cache->get($id . ':k', true)) {
            foreach ($keys as $key => $val) {
                $this->cache->delete($key);
            }
            $this->cache->delete($id . ':k');
        }
        if ($element = $this->getElementById($id)) {
            $this->cache->clearKeysByType($id, $element->structureType);
        }
    }

    public function createElementCache($elementId): void
    {
        if ($this->elementsList[$elementId]) {
            $this->setElementCacheKey($elementId, 'e', $this->elementsList[$elementId], $this->cacheLifeTime);
        }
    }

    public function getRequestedPath(): array
    {
        return $this->requestedPath;
    }


}
