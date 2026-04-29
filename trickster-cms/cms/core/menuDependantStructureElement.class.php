<?php

/**
 * Class menuDependantStructureElement
 *
 * @property array $displayMenus
 */
abstract class menuDependantStructureElement extends structureElement implements MetadataProviderInterface
{
    use MetadataProviderTrait;
    protected $menusInfo;
    protected $menusInfoMobile;

    protected function initialize()
    {
        $this->moduleFields['displayMenus'] = 'array';
        return true;
    }

    public function persistElementData()
    {
        parent::persistElementData();
        $this->checkDisplayMenus();
    }

    public function checkDisplayMenus()
    {
        $result = false;
        $controller = controller::getInstance();
        if ($controller->getParameter('view')) {
            $result = true;
            $view = $controller->getParameter('view');

            $this->URL = $this->URL . 'view:' . $view . '/';
        }
        return $result;
    }

    public function getDisplayMenusInfo()
    {
        if ($this->menusInfo === null) {
            $this->menusInfo = $this->loadLinksInfo('displayinmenu');
        }
        return $this->menusInfo;
    }

    protected function loadLinksInfo($linkType)
    {
        $menusInfo = [];
        $structureManager = $this->getService('structureManager');
        $linksManager = $this->getService(linksManager::class);
        $linksIndex = $linksManager->getElementsLinksIndex($this->id, $linkType, 'child');

        if ($publicRoot = $structureManager->getElementByMarker($this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic'))
        ) {
            if ($languagesList = $structureManager->getElementsChildren($publicRoot->id)) {
                foreach ($languagesList as &$languageElement) {
                    if ($languageElement->requested) {
                        $this->processMenuInfoItem($languageElement, $linksIndex, $menusInfo);
                        break;
                    }
                }
            }
        }
        return $menusInfo;
    }

    /**
     * @param structureElement $element
     * @param $linksIndex
     * @param $menusInfo
     */
    protected function processMenuInfoItem($element, $linksIndex, &$menusInfo)
    {
        $info = [
            'title' => $element->getTitle(),
            'id' => $element->id,
            'level' => $element->level - 2,
            'select' => false,
        ];
        if (isset($linksIndex[$element->id])) {
            $info['select'] = true;
        }
        $menusInfo[] = $info;
        $structureManager = $this->getService('structureManager');
        if ($subMenus = $structureManager->getElementsChildren($element->id, ['container', 'hybrid'], ['structure', 'catalogue'])) {
            foreach ($subMenus as &$subMenu) {
                $this->processMenuInfoItem($subMenu, $linksIndex, $menusInfo);
            }
        }
    }

    public function persistDisplayMenusLinks()
    {
        $linksManager = $this->getService(linksManager::class);
        $linksIndex = $linksManager->getElementsLinksIndex($this->id, 'displayinmenu', 'child');
        foreach ($this->displayMenus as $menuId) {
            if (!isset($linksIndex[$menuId])) {
                $linksManager->linkElements($menuId, $this->id, 'displayinmenu');
            } else {
                unset($linksIndex[$menuId]);
            }
        }
        foreach ($linksIndex as $link) {
            $link->delete();
        }
    }

    public function getFormActionURL($type = null)
    {
        $controller = controller::getInstance();
        if ($controller->getParameter('view') && (stripos($this->URL, 'view:') === false)) {
            return $this->URL . 'view:' . $controller->getParameter('view') . '/';
        } else {
            return $this->URL;
        }
    }

    public function getNewElementUrl()
    {
        $controller = controller::getInstance();
        $url = $this->URL;

        if ($controller->getParameter('view') && $this->final) {
            $contentType = $controller->getParameter('view');
            $urlString = 'view:' . $contentType . '/';

            $url = str_ireplace($urlString, '', $url);
        }

        return $url;
    }

    public function getChildrenList($roles = null, $linkType = 'structure', $allowedTypes = null, $restrictLinkTypes = false)
    {
        $childrenList = parent::getChildrenList($roles, $linkType, $allowedTypes, $restrictLinkTypes);

        $controller = controller::getInstance();
        $applicationName = $controller->getApplicationName();
        if ($applicationName != 'public') {
            $contentType = $controller->getParameter('view') ? $controller->getParameter('view') : 'structure';

            if ($contentType != 'structure') {
                $urlString = 'view:' . $contentType . '/';
                foreach ($childrenList as $element) {
                    $element->URL = str_ireplace($urlString, '', $element->URL);
                }
            }
        }
        return $childrenList;
    }
}