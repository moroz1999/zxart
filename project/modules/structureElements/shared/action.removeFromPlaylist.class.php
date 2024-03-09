<?php

class removeFromPlaylistShared extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($playlistId = $controller->getParameter('playlistId')) {
            $linksManager = $this->getService('linksManager');
            $linksManager->unLinkElements(intval($playlistId), $structureElement->id, 'playlist');
        }

        $renderer = $this->getService('renderer');
        if ($renderer instanceof rendererPluginAppendInterface) {
            $data = $structureElement->getElementData();
            $data['playlistIds'] = $structureElement->getPlaylistIds();
            $renderer->appendResponseData($structureElement->structureType, $data);
        }
    }
}

