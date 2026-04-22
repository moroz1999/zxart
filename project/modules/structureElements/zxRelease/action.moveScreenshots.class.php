<?php

class moveScreenshotsZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxReleaseElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $linksManager = $this->getService(linksManager::class);
        $prod = $structureElement->getProd();
        if ($prod === null) {
            throw new \RuntimeException('Prod is not set');
        }
        $prodId = $prod->id;
        foreach ($structureElement->getFilesList('screenshotsSelector') as $screenShot) {
            $linksManager->unLinkElements($structureElement->getId(), $screenShot->id, 'screenshotsSelector');
            $linksManager->linkElements($prodId, $screenShot->id, 'connectedFile');
            $structureManager->clearElementCache($screenShot->id);
        }
        $structureManager->clearElementCache($prodId);
        $structureManager->clearElementCache($structureElement->getId());

        $prod = $structureElement->getProd();
        if ($prod === null) {
            throw new \RuntimeException('Prod is not set');
        }
        $controller->redirect($prod->getUrl());
    }
}