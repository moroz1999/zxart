<?php

class showFolder extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param folderElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->externalUrl) {
            $structureElement->URL = $structureElement->externalUrl;
        }

        if ($structureElement->requested) {
            $structureElement->setViewName('content');
            if ($structureElement->final) {
                $application = $controller->getApplicationName();
                if ($application == 'public' || $application == 'mobile') {
                    if (method_exists($structureElement, 'getSubMenuList')) {
                        if (!$structureElement->getContentElements() && ($subMenu = $structureElement->getSubMenuList(
                                null
                            ))) {
                            $firstMenu = reset($subMenu);
                            $controller->restart($firstMenu->URL);
                        }
                    }
                }
            }

            if ($structureElement->final) {
                $application = $controller->getApplication();
                if ($application instanceof publicApplication) {
                    $subMenu = $structureElement->getSubMenuList();
                    if (!($structureElement->getContentElements()) && $subMenu) {
                        $firstMenu = reset($subMenu);
                        $controller->restart($firstMenu->URL);
                    }
                }
            }
        }
    }
}

