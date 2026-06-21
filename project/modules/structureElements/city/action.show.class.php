<?php

class showCity extends structureElementAction
{
    /**
     * @param cityElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $isCurrentElement = $structureManager->getCurrentElement() === $structureElement;
        if ($isCurrentElement === true && $controller->getApplication() instanceof publicApplication) {
            $redirectUrl = $structureElement->getUrl();
            if ($redirectUrl !== $structureElement->URL) {
                $controller->redirect($redirectUrl, '301');
            }
        }

        $structureElement->setViewName('content');
    }
}

