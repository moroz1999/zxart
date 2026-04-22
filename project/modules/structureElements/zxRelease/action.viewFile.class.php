<?php

class viewFileZxRelease extends structureElementAction
{
    /**
     * @param zxReleaseElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if (!$structureElement->getCurrentReleaseFileInfo()) {
            $this->getService(renderer::class)->fileNotFound();
        }
        $structureElement->setViewName('fileDetails');
    }
}