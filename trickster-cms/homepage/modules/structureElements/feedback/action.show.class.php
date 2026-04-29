<?php

class showFeedback extends structureElementAction
{
    /**
     * @param feedbackElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            if ($controller->getParameter("product")) {
                $productId = (int)$controller->getParameter("product");
                $structureElement->setProductId($productId);
            }
            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->id)) {
                $structureManager->setCurrentElement($parentElement);
            }
        }
        $structureElement->setViewName('form');
    }

    public function getExtraModuleFields()
    {
        return $this->structureElement->getCustomModuleFields();
    }
}