<?php

class getPlaylistIdsShared extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $renderer = $this->getService('renderer');
        if ($renderer instanceof rendererPluginAppendInterface) {
            $data = $structureElement->getElementData();
            $data['playlistIds'] = $structureElement->getPlaylistIds();
            $renderer->appendResponseData($structureElement->structureType, $data);
        }
    }
}

