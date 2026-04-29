<?php

class receiveSubMenuList extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param subMenuListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $linksManager = $this->getService(linksManager::class);
            $linksIndex = $linksManager->getElementsLinksIndex($structureElement->id, 'submenulist', 'parent');
            foreach ($structureElement->menus as $menuId) {
                $linksManager->linkElements($structureElement->id, $menuId, 'submenulist', true);
                unset($linksIndex[$menuId]);
            }
            foreach ($linksIndex as &$link) {
                $link->delete();
            }

            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();

            $structureElement->persistDisplayMenusLinks();

            $controller->redirect($structureElement->URL);
        }
        $structureElement->executeAction('showForm');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'type',
            'menus',
            'displayMenus',
            'maxLevels',
            'skipLevels',
            'levels',
            'popup',
            'displayHeadingAutomatically',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}