<?php

use ZxArt\Releases\ItchIo\ItchIoReleasesImporter;

class importItchIoReleasesZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $added = $this->getService(ItchIoReleasesImporter::class)->import($structureElement);

        // the action is run by the SPA through /ajax/ only
        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof RendererPluginAppendInterface) {
            $renderer->assign('body', ['id' => $structureElement->getId(), 'success' => $added > 0]);
        }
    }
}
