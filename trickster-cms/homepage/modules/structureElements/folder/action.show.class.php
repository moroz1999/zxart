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

