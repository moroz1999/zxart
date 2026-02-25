<?php

class addToPlaylistShared extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
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

