<?php

class getPlaylistIdsShared extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof RendererPluginAppendInterface) {
            $data = $structureElement->getElementData();
            $data['playlistIds'] = $structureElement->getPlaylistIds();
            $renderer->appendResponseData($structureElement->structureType, $data);
        }
    }
}

