<?php

class subMenuListElement extends menuStructureElement implements ConfigurableLayoutsProviderInterface
{
    use ConfigurableLayoutsProviderTrait;
    use CacheOperatingElement;

    public $dataResourceName = 'module_submenulist';
    protected $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    public $menusList = [];

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['type'] = 'text';
        $moduleStructure['layout'] = 'text';
        $moduleStructure['columnLayout'] = 'text';
        $moduleStructure['menus'] = 'array';

        $moduleStructure['levels'] = 'text';
        $moduleStructure['skipLevels'] = 'text';
        $moduleStructure['maxLevels'] = 'text';
        $moduleStructure['popup'] = 'text';

        $moduleStructure['displayHeadingAutomatically'] = 'text';
    }

    protected function getTabsList()
    {
        return [
            'showForm',
            'showLayoutForm',
            'showPositions',
            'showPrivileges',
        ];
    }

    /**
     * Generates and returns a complete list of all submenus according to all settings and parameters
     *
     * @param string $linkType
     * @return structureElement[]
     */
    public function getSubMenuList($linkType = 'structure')
    {
        if ($this->subMenuList === null) {
            $structureManager = $this->getService('structureManager');
            if ($currentElement = $structureManager->getCurrentElement()) {
                $key = 'il:' . $currentElement->id;
            } else {
                $key = 'il:0';
            }
            $cache = $this->getElementsListCache($key, 36000);
            if (($this->subMenuList = $cache->load()) === null) {

                $this->subMenuList = [];
                $directSubMenuList = [];

                $linksManager = $this->getService(linksManager::class);
                /**
                 * @var structureManager $structureManager
                 */
                $languagesManager = $this->getService(LanguagesManager::class);
                $currentLanguageId = $languagesManager->getCurrentLanguageId();

                if ($this->type == 'select') {
                    //gather manually selected submenus
                    if ($connectedIds = $linksManager->getConnectedIdList($this->id, 'submenulist', 'parent')) {
                        foreach ($connectedIds as $elementId) {
                            if ($element = $structureManager->getElementById($elementId, $languagesManager->getCurrentLanguageId())) {
                                if ($element->structureType !== 'search') {
                                    $directSubMenuList[] = $element;
                                }
                            }
                        }
                    }
                } else {
                    //gather all submenus automatically
                    if ($languagesElement = $structureManager->getElementById($currentLanguageId)) {
                        foreach ($structureManager->getElementsChildren($languagesElement->id, 'container', null, null, true) as $childElement) {
                            if (!$childElement->hidden && $childElement->structureType !== 'search') {
                                $directSubMenuList[] = $childElement;
                            }
                        }
                    }
                }
                //if we want to skip some top-levels, then filter them out from results.
                if ($directSubMenuList && $this->skipLevels) {
                    $this->subMenuList = $this->skipLevels($directSubMenuList, $this->skipLevels);
                } //otherwise just return direct results.
                else {
                    $this->subMenuList = $directSubMenuList;
                }
                $cache->save($this->subMenuList);
            }
        }
        return $this->subMenuList;
    }

    /**
     * Filters out unneeded top-levels from the submenus tree.
     * The result of filtration is a tree of elements starting from the children under the designated nested level.
     *
     * @param structureElement[] $levelSubMenuList
     * @param int $skipLevels
     * @return structureElement[]
     */
    protected function skipLevels($levelSubMenuList, $skipLevels)
    {
        if ($skipLevels) {
            $elements = [];
            foreach ($levelSubMenuList as $element) {
                if ($element->requested && method_exists($element, "getSubMenuList")) {
                    if ($subMenuList = $element->getSubMenuList()) {
                        $elements = array_merge($elements, $this->skipLevels($subMenuList, $skipLevels - 1));
                    }
                }
            }
            return $elements;
        } else {
            return $levelSubMenuList;
        }
    }

    public function getMenusInfo()
    {
        $menusInfo = [];
        $menusInfo[] = [
            'title' => $this->title,
            'id' => $this->id,
            'URL' => $this->URL,
            'parentId' => 0,
        ];
        if ($subMenuList = $this->getSubMenuList()) {
            foreach ($subMenuList as &$menuItem) {
                $this->getMenuItemInfo($menuItem, $menusInfo, 1);
            }
        }
        return $menusInfo;
    }

    protected function getMenuItemInfo($menuItem, &$menusInfo, $level)
    {
        $menusInfo[] = [
            'title' => $menuItem->title,
            'id' => $menuItem->id,
            'URL' => $menuItem->URL,
            'parentId' => 0,
        ];
        if (method_exists($menuItem, "getSubMenuList")) {
            if ($subMenuList = $menuItem->getSubMenuList()) {
                foreach ($subMenuList as &$subMenuItem) {
                    if ($this->levels == $level) {
                        $menusInfo[] = [
                            'title' => $subMenuItem->title,
                            'id' => $subMenuItem->id,
                            'URL' => $subMenuItem->URL,
                            'parentId' => $menuItem->id,
                        ];
                    }
                    if ($this->levels > $level) {
                        $this->getMenuItemInfo($subMenuItem, $menusInfo, $level + 1);
                    }
                }
            }
        }
        return $menusInfo;
    }

    public function getMenuLinkTypes()
    {
        return [
            'structure',
            'catalogue',
            'countriesListCountry',
        ];
    }

    public function getMenusInfoForPopup()
    {
        $menusInfo = [];
        $menusInfo[] = [
            'title' => $this->title,
            'id' => $this->id,
            'URL' => $this->URL,
            'parentId' => 0,
        ];
        if ($subMenuList = $this->getSubMenuList()) {
            foreach ($subMenuList as &$menuItem) {
                $this->getMenuItemInfoForPopup($menuItem, $menusInfo, 1);
            }
        }
        return $menusInfo;
    }

    protected function getMenuItemInfoForPopup($menuItem, &$menusInfo, $level)
    {
        if (!$menuItem->hidden) {
            if ($level == 1) {
                $menusInfo[] = [
                    'title' => $menuItem->title,
                    'id' => $menuItem->id,
                    'URL' => $menuItem->URL,
                    'parentId' => 0,
                ];
            }
            if (method_exists($menuItem, "getSubMenuList")) {
                if ($subMenuList = $menuItem->getSubMenuList()) {
                    foreach ($subMenuList as &$subMenuItem) {
                        if (!$subMenuItem->hidden) {
                            $menusInfo[] = [
                                'title' => $subMenuItem->title,
                                'id' => $subMenuItem->id,
                                'URL' => $subMenuItem->URL,
                                'parentId' => $menuItem->id,
                            ];

                            if ($this->levels > $level) {
                                $this->getMenuItemInfoForPopup($subMenuItem, $menusInfo, $level + 1);
                            }
                        }
                    }
                }
            }
        }
        return $menusInfo;
    }
}