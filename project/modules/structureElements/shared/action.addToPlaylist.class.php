<?php

class addToPlaylistShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($playlistId = $controller->getParameter('playlistId')) {
            $linksManager = $this->getService(linksManager::class);
            $linksManager->linkElements(intval($playlistId), $structureElement->getId(), 'playlist');
        }

        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof RendererPluginAppendInterface) {
            $data = $structureElement->getElementData();
            $data['playlistIds'] = $structureElement->getPlaylistIds();
            $renderer->appendResponseData($structureElement->structureType, $data);
        }
    }
}

