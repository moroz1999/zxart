<?php

/**
 * Class languageElement
 *
 * @property string $title
 * @property string $iso6393
 * @property string $logoImage
 * @property string $logoImageOriginalName
 */
class languageElement extends structureElement implements MetadataProviderInterface, BreadcrumbsInfoProvider
{
    use CacheOperatingElement;
    use MetadataProviderTrait;

    public $dataResourceName = 'module_language';
    public $defaultActionName = 'show';
    public $role = 'container';
    protected $leftColumnElementsList;
    protected $rightColumnElementsList;
    protected $headerElementsList;
    protected $headerElementsIndex;
    protected $footerElementsList;
    protected $footerElementsIndex;
    protected $mobileMenuElementsList;
    protected $mobileMenuElementsIndex;
    protected $votesHistory;
    protected $sectionsList;
    protected $currentSection;
    protected $elementsIndex;
    protected $elementsList;
    protected $currentMainMenu;
    protected $mainMenuElements;
    protected $firstPageElement;

    /**
     * @return string[]
     *
     * @psalm-return list{'showFullList', 'showForm', 'headerContent', 'leftColumn', 'rightColumn', 'bottomMenu', 'mobileMenu', 'showPositions', 'showPrivileges'}
     */
    protected function getTabsList()
    {
        return [
            'showFullList',
            'showForm',
            'headerContent',
            'leftColumn',
            'rightColumn',
            'bottomMenu',
            'mobileMenu',
            'showPositions',
            'showPrivileges',
        ];
    }

    public function getMainMenuElements(bool $ignoreHidden = false)
    {
        if ($ignoreHidden) {
            if ($childElements = $this->getChildrenList(null, ['structure', 'catalogue'])) {
                return $childElements;
            }
        } elseif ($this->mainMenuElements === null) {
            $this->mainMenuElements = [];
            if ($childElements = $this->getChildrenList()) {
                foreach ($childElements as $childElement) {
                    if (!empty($childElement->hidden)) {
                        $this->mainMenuElements[] = $childElement;
                    }
                }
            }
        }
        return $this->mainMenuElements;
    }

    public function getFirstPageElement()
    {
        if ($this->firstPageElement === null) {
            $structureManager = $this->getService('structureManager');
            if ($mainMenuElements = $structureManager->getElementsChildren($this->id, 'container')) {
                $this->firstPageElement = reset($mainMenuElements);
            } else {
                $this->firstPageElement = false;
            }
        }
        return $this->firstPageElement;
    }

    public function getCurrentMainMenu()
    {
        if ($this->currentMainMenu === null) {
            $this->currentMainMenu = false;
            if ($mainMenuElements = $this->getMainMenuElements(true)) {
                foreach ($mainMenuElements as $element) {
                    if ($element->requested) {
                        $this->currentMainMenu = $element;
                        break;
                    }
                }
            }
            if (!$this->currentMainMenu) {
                $structureManager = $this->getService('structureManager');
                $controller = $this->getService('controller');
                if ($chain = $structureManager->getElementsChain($controller->requestedPath)) {
                    $this->currentMainMenu = last($chain);
                }
            }
        }
        return $this->currentMainMenu;
    }

    public function getTextContent()
    {
        if (is_null($this->textContent)) {
            $this->textContent = $this->title . " (" . $this->iso6393 . ")";
        }
        return $this->textContent;
    }

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['iso6391'] = 'text';
        $moduleStructure['iso6393'] = 'text';
        $moduleStructure['group'] = 'text';
        $moduleStructure['originalName'] = 'fileName';
        $moduleStructure['image'] = 'image';
        $moduleStructure['backgroundImageOriginalName'] = 'fileName';
        $moduleStructure['backgroundImage'] = 'image';
        $moduleStructure['logoImageOriginalName'] = 'fileName';
        $moduleStructure['logoImage'] = 'image';
        $moduleStructure['hidden'] = 'checkbox';
        $moduleStructure['patternBackground'] = 'checkbox';
    }

    public function getElementFromMobileMenu($structureType, $number = 0)
    {
        return $this->getElementFromContextByType('mobileMenu', $structureType, $number);
    }

    public function getElementsFromMobileMenu($structureType)
    {
        return $this->getElementsFromContextByType('mobileMenu', $structureType);
    }

    public function getMobileMenuElementsList()
    {
        return $this->getElementsFromContext('mobileMenu');
    }

    public function getMobileMenuElementsIndex()
    {
        return $this->getElementsTypesIndexFromContext('mobileMenu');
    }

    public function getElementFromMobileHeader(): void
    {
        $this->logError('Deprecated method used ' . __CLASS__ . ' ' . __METHOD__);
    }

    public function getHeaderElementsList()
    {
        return $this->getElementsFromContext('headerContent');
    }

    public function getHeaderElementsIndex()
    {
        return $this->getElementsTypesIndexFromContext('headerContent');
    }

    /**
     * @psalm-param 'gallery' $structureType
     */
    public function getElementFromHeader(string $structureType, $number = 0)
    {
        return $this->getElementFromContextByType('headerContent', $structureType, $number);
    }

    /**
     * @psalm-param 'gallery' $structureType
     */
    public function getElementsFromHeader(string $structureType)
    {
        return $this->getElementsFromContextByType('headerContent', $structureType);
    }

    public function getFooterElementsList()
    {
        return $this->getElementsFromContext('bottomMenu');
    }

    public function getFooterElementsIndex()
    {
        return $this->getElementsTypesIndexFromContext('bottomMenu');
    }

    public function getElementFromFooter($structureType, $number = 0)
    {
        return $this->getElementFromContextByType('bottomMenu', $structureType, $number);
    }

    public function getElementsFromFooter($structureType)
    {
        return $this->getElementsFromContextByType('bottomMenu', $structureType);
    }

    public function getSecondaryElements(): array
    {
        return array_merge($this->getHeaderElementsList(), $this->getLeftColumnElementsList());
    }

    public function getLeftColumnElementsList()
    {
        $result = [];
        if ($currentMainMenu = $this->getCurrentMainMenu()) {
            if ($currentMainMenu instanceof ColumnsTypeProvider) {
                $columnsType = $currentMainMenu->getColumnsType();
                if ($columnsType == 'both' || $columnsType == 'left') {
                    $result = $this->getElementsFromContext('leftColumn');
                }
            } elseif (!empty($currentMainMenu->columns)) {
                //todo: remove after 04.2021
                $this->logError('Deprecated direct property "columns" access. Implement ColumnsTypeProvider instead');
                if ($currentMainMenu->columns == 'both' || $currentMainMenu->columns == 'left') {
                    $result = $this->getElementsFromContext('leftColumn');
                }
            }
        }
        return $result;
    }

    public function getElementFromLeftColumn($structureType, $number = 0)
    {
        foreach ($this->getLeftColumnElementsList() as $element) {
            if ($element->structureType == $structureType) {
                if ($number == 0) {
                    return $element;
                }
                $number--;
            }
        }
        return false;
    }

    public function getElementFromRightColumn($structureType, $number = 0)
    {
        foreach ($this->getRightColumnElementsList() as $element) {
            if ($element->structureType == $structureType) {
                if ($number == 0) {
                    return $element;
                }
                $number--;
            }
        }
        return false;
    }

    public function getRightColumnElementsList()
    {
        $result = [];
        if ($currentMainMenu = $this->getCurrentMainMenu()) {
            if ($currentMainMenu instanceof ColumnsTypeProvider) {
                $columnsType = $currentMainMenu->getColumnsType();
                if ($columnsType == 'both' || $columnsType == 'right') {
                    $result = $this->getElementsFromContext('rightColumn');
                }
            } elseif (!empty($currentMainMenu->columns)) {
                //todo: remove after 04.2021
                $this->logError('Deprecated direct property "columns" access. Implement ColumnsTypeProvider instead');
                if ($currentMainMenu->columns == 'both' || $currentMainMenu->columns == 'right') {
                    $result = $this->getElementsFromContext('rightColumn');
                }
            }
        }
        return $result;
    }

    /**
     * @return void
     */
    protected function loadElements($context)
    {
        if (isset($this->elementsList[$context])) {
            return;
        }
        $this->elementsList[$context] = [];
        $this->elementsIndex[$context] = [];
        $elements = &$this->elementsList[$context];
        $currentMainMenu = $this->getCurrentMainMenu();
        if ($currentMainMenu) {
            $elements = $this->getEnabledElementsByType($currentMainMenu->id, $context);
        }
        foreach ($elements as $element) {
            if (!isset($this->elementsIndex[$context][$element->structureType])) {
                $this->elementsIndex[$context][$element->structureType] = [];
            }
            $this->elementsIndex[$context][$element->structureType][] = $element;
        }
    }

    protected function getElementsFromContext(string $context)
    {
        $this->loadElements($context);
        return isset($this->elementsList[$context])
            ? $this->elementsList[$context] : [];
    }

    /**
     * @psalm-param 'bottomMenu'|'headerContent'|'mobileMenu' $context
     */
    protected function getElementsTypesIndexFromContext(string $context)
    {
        $this->loadElements($context);
        return isset($this->elementsIndex[$context])
            ? $this->elementsIndex[$context] : [];
    }

    /**
     * @psalm-param 'bottomMenu'|'headerContent'|'mobileMenu' $context
     */
    protected function getElementsFromContextByType(string $context, $type)
    {
        $this->loadElements($context);
        return isset($this->elementsIndex[$context][$type])
            ? $this->elementsIndex[$context][$type] : [];
    }

    /**
     * @psalm-param 'bottomMenu'|'headerContent'|'mobileMenu' $context
     */
    protected function getElementFromContextByType(string $context, $type, $number = 0)
    {
        $elements = $this->getElementsFromContextByType($context, $type);
        return isset($elements[$number]) ? $elements[$number] : [];
    }

    protected function getEnabledElementsByType($menuId, $type)
    {
        $result = [];
        $linkType = 'displayinmenu';

        $linksManager = $this->getService('linksManager');
        $structureManager = $this->getService('structureManager');

        $enabledElementsIdList = $linksManager->getConnectedIdList($this->id, $type, 'parent');
        $languageEnabledElementsIdList = $linksManager->getConnectedIdList($this->id, $linkType, 'parent');
        $currentMenuEnabledElementsIdList = $linksManager->getConnectedIdList($menuId, $linkType, 'parent');

        // todo: this now only supports the direct children of current menu. We should somehow allow any level to be used
        if ($currentMainPage = $this->getCurrentMainMenu()) {
            if (!$currentMainPage->final) {
                $subPages = $currentMainPage->getChildrenList();
                foreach ((array)$subPages as $subPage) {
                    if ($subPage->requested) {
                        $currentMenuEnabledElementsIdList = array_merge(
                            $currentMenuEnabledElementsIdList,
                            (array)($linksManager->getConnectedIdList(
                                $subPage->id,
                                $linkType,
                                'parent'
                            ))
                        );
                        break;
                    }
                }
            }
        }

        if ($resultIdList = array_intersect(
            array_merge($languageEnabledElementsIdList, $currentMenuEnabledElementsIdList),
            $enabledElementsIdList
        )) {
            $result = $structureManager->getElementsByIdList($resultIdList, $this->id, true);
        }
        return $result;
    }

    public function getChildrenList(
        $roles = null,
        $linkType = 'structure',
        $allowedTypes = null,
        $restrictLinkTypes = false
    )
    {
        $controller = controller::getInstance();
        $applicationName = $controller->getApplicationName();
        // TODO: perhaps check for admin app instead
        if ($applicationName == 'public') {
            return parent::getChildrenList($roles, $linkType, $allowedTypes, $restrictLinkTypes);
        } else {
            //todo: review this code and remove $urlString
            $structureManager = $this->getService('structureManager');
            $linksManager = $this->getService('linksManager');
            $contentType = $controller->getParameter('view') ? $controller->getParameter('view') : 'structure';

            $idList = $linksManager->getConnectedIdList($this->id, $contentType, 'parent');
            $childrenList = $structureManager->getElementsByIdList($idList, $this->id, true);
            if ($contentType != 'structure') {
                $urlString = 'view:' . $contentType . '/';
                foreach ($childrenList as $element) {
                    if (!stripos($element->URL, $urlString)) {
                        $element->URL .= $urlString;
                    }
                }
            }

            return $childrenList;
        }
    }

    public function getNewElementUrl()
    {
        $controller = controller::getInstance();
        $url = $this->URL;
        if ($controller->getApplicationName() != 'adminAjax') {
            if ($controller->getParameter('view') && $this->final) {
                $contentType = $controller->getParameter('view');
                $url .= 'view:' . $contentType . '/';
            }
        }
        return $url;
    }

    /**
     * @return array
     * @deprecated
     */
    public function getMenuElements()
    {
        return $this->getMainMenuElements();
    }

    public function getFormActionURL($type = null)
    {
        $controller = controller::getInstance();
        if ($contentType = $controller->getParameter('view')) {
            return $this->URL . 'view:' . $contentType . '/';
        }

        return $this->URL;
    }

    /**
     * Get allowed children structure elements type according to settings, current user's privileges and selected type
     *
     * @param string $currentAction
     * @return string[]
     */
    public function getAllowedTypes($currentAction = 'showFullList')
    {
        if ($this->allowedTypesByAction[$currentAction] === null) {
            $this->allowedTypesByAction[$currentAction] = [];

            $childCreationAction = 'showForm';

            $contentType = 'structure';
            $controller = controller::getInstance();
            if ($controller->getApplicationName() != 'adminAjax') {
                if ($controller->getParameter('view')) {
                    $contentType = $controller->getParameter('view');
                }
            }
            /**
             * @var ConfigManager $configManager
             */
            $configManager = $this->getService('ConfigManager');
            if ($contentType == 'headerContent') {
                $allowedTypes = $configManager->getMerged('language-allowedTypes.header');
            } elseif ($contentType == 'bottomMenu') {
                $allowedTypes = $configManager->getMerged('language-allowedTypes.footer');
            } elseif ($contentType == 'mobileMenu') {
                $allowedTypes = $configManager->getMerged('language-allowedTypes.mobile');
            } elseif ($contentType != 'structure') {
                $allowedTypes = $configManager->getMerged('language-allowedTypes.columns');
            } else {
                $allowedTypes = $configManager->getMerged('language-allowedTypes.content');
            }

            $privilegesManager = $this->getService('privilegesManager');
            $privileges = $privilegesManager->getElementPrivileges($this->id);

            foreach ($allowedTypes as $type) {
                if (isset($privileges[$type]) && isset($privileges[$type][$childCreationAction]) && $privileges[$type][$childCreationAction] === true) {
                    $this->allowedTypesByAction[$currentAction][] = $type;
                }
            }
        }
        return $this->allowedTypesByAction[$currentAction];
    }

    public function getMostSuitableHeaderGallery()
    {
        $controller = controller::getInstance();
        $structureManager = $this->getService('structureManager');
        $currentElement = $structureManager->getCurrentElement($controller->requestedPath);
        $galleries = $this->getElementsFromHeader('gallery');
        $betterGallery = false;

        foreach ($galleries as $gallery) {
            foreach ($gallery->getDisplayMenusInfo() as $info) {
                if ($info['id'] == $currentElement->id && $info['linkExists']) {
                    $betterGallery = $gallery;
                }
            }
        }

        if ($betterGallery) {
            return $betterGallery;
        } else {
            return $this->getElementFromHeader('gallery');
        }
    }

    public function getLogoImageUrl($preset = 'logo'): string
    {
        $result = '';
        if ($this->logoImage) {
            $controller = controller::getInstance();
            $result = $controller->baseURL . 'image/type:' . $preset . '/id:' . $this->logoImage
                . '/filename:' . $this->logoImageOriginalName;
        }

        if (!$result) {
            /**
             * @var DesignThemesManager $designThemesManager
             */
            $designThemesManager = $this->getService('DesignThemesManager');
            $configManager = $this->getService('ConfigManager');

            if ($theme = $designThemesManager->getTheme($configManager->get('main.publicTheme'))) {
                $result = $theme->getImageUrl('logo.png', false, false);
            }
        }

        return $result;
    }

    public function clearCommentsCache(): void
    {
        $this->deleteCache('lc');
    }

    public function getLatestComments($limit = 10)
    {
        $cache = $this->getElementsListCache('lc', 180);
        if (($comments = $cache->load()) === null) {
            $comments = [];

            $structureManager = $this->getService('structureManager');
            $collection = persistableCollection::getInstance('structure_elements');
            $now = time();

            $columns = ['id'];

            $conditions = [];
            $conditions[] = ['column' => 'dateCreated', 'action' => '<=', 'argument' => $now];
            $conditions[] = ['column' => 'structureType', 'action' => '=', 'argument' => 'comment'];

            $orderFields = ['dateCreated' => 0];

            $result = $collection->conditionalLoad($columns, $conditions, $orderFields, $limit);
            foreach ($result as $row) {
                if ($comment = $structureManager->getElementById($row['id'])) {
                    $comments[] = $comment;
                }
            }
            $cache->save($comments);
        }
        return $comments;
    }


    public function getLatestVotes($limit = 10)
    {
        if ($this->votesHistory === null) {
            if (!$this->votesHistory = $this->getCacheKey('votes')) {
                $this->votesHistory = $this->getService('votesManager')->getLatestVotes($limit);
                $this->setCacheKey('v', $this->votesHistory, 300);
            }
        }
        return $this->votesHistory;
    }

    public function getLoadedImages()
    {
        $images = $this->getService('structureManager')->getLoadedElementsByType('zxPicture');
        return $images;
    }

    public function getSectionsList()
    {
        if ($this->sectionsList === null) {
            $this->filterTopMenus();
        }
        return $this->sectionsList;
    }

    protected function filterTopMenus(): void
    {
        $this->sectionsList = [];

        $structureManager = $this->getService('structureManager');
        if ($menus = $structureManager->getElementsChildren($this->id, 'container')) {
            foreach ($menus as $menu) {
                if ($menu->marker === 'graphics' || $menu->marker === 'music' || $menu->marker === 'software') {
                    $this->sectionsList[] = $menu;
                }
            }
        }
    }

    public function getCurrentSection()
    {
        if ($this->currentSection === null) {
            $sections = $this->getSectionsList();
            $this->currentSection = false;
            foreach ($sections as $section) {
                if ($section->requested) {
                    $this->currentSection = $section;
                }
            }
        }
        return $this->currentSection;
    }

    public function getBreadcrumbsTitle(): string
    {
        $firstPageElement = $this->getFirstPageElement();
        if ($firstPageElement) {
            return $firstPageElement->getTitle();
        }
        return $this->getTitle();
    }

    public function getBreadcrumbsUrl(): string
    {
        $firstPageElement = $this->getFirstPageElement();
        if ($firstPageElement) {
            return $firstPageElement->getUrl();
        }
        return $this->getUrl();
    }

    public function isBreadCrumb(): bool
    {
        return true;
    }
}
