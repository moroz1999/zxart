<?php

class moveScreenshotsZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxReleaseElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $linksManager = $this->getService(linksManager::class);
        $prodId = $structureElement->getProd()->id;
        foreach ($structureElement->getFilesList('screenshotsSelector') as $screenShot) {
            $linksManager->unLinkElements($structureElement->id, $screenShot->id, 'screenshotsSelector');
            $linksManager->linkElements($prodId, $screenShot->id, 'connectedFile');
            $structureManager->clearElementCache($screenShot->id);
        }
        $structureManager->clearElementCache($prodId);
        $structureManager->clearElementCache($structureElement->id);

        $controller->redirect($structureElement->getProd()->URL);
    }
}