<?php

class viewFileZxRelease extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxReleaseElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if (!$structureElement->getCurrentReleaseFile()) {
            $this->getService('renderer')->fileNotFound();
        }
        $structureElement->setViewName('fileDetails');
    }
}