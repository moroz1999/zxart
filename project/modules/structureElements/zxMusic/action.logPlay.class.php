<?php

class logPlayZxMusic extends structureElementAction
{
    /**
     * @param zxMusicElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->logPlay();

        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof RendererPluginAppendInterface) {
            $renderer->appendResponseData('zxMusic', $structureElement->getElementData());
        }
    }
}

