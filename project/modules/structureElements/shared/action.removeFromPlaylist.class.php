<?php

class removeFromPlaylistShared extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($playlistId = $controller->getParameter('playlistId')) {
            $linksManager = $this->getService(linksManager::class);
            $linksManager->unLinkElements(intval($playlistId), $structureElement->getId(), 'playlist');
        }

        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof rendererPluginAppendInterface) {
            $data = $structureElement->getElementData();
            $data['playlistIds'] = $structureElement->getPlaylistIds();
            $renderer->appendResponseData($structureElement->structureType, $data);
        }
    }
}

