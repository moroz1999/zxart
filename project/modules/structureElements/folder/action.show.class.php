<?php

class showFolder extends structureElementAction
{
    /**
     * @param folderElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
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

