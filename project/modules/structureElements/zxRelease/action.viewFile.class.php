<?php

class viewFileZxRelease extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxReleaseElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if (!$structureElement->getCurrentReleaseFileInfo()) {
            $this->getService(renderer::class)->fileNotFound();
        }
        $structureElement->setViewName('fileDetails');
    }
}