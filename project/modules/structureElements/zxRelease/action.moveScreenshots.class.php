<?php

class moveScreenshotsZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxReleaseElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $linksManager = $this->getService(linksManager::class);
        $prod = $structureElement->getProd();
        if ($prod === null) {
            throw new \RuntimeException('Prod is not set');
        }
        $prodId = $prod->id;
        foreach ($structureElement->getFilesList('screenshotsSelector') as $screenShot) {
            $linksManager->unLinkElements($structureElement->id, $screenShot->id, 'screenshotsSelector');
            $linksManager->linkElements($prodId, $screenShot->id, 'connectedFile');
            $structureManager->clearElementCache($screenShot->id);
        }
        $structureManager->clearElementCache($prodId);
        $structureManager->clearElementCache($structureElement->id);

        $prod = $structureElement->getProd();
        if ($prod === null) {
            throw new \RuntimeException('Prod is not set');
        }
        $controller->redirect($prod->getUrl());
    }
}