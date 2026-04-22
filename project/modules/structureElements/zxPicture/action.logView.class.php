<?php

class logViewZxPicture extends structureElementAction
{
    /**
     * @param zxPictureElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->logView();
        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof RendererPluginAppendInterface) {
            $renderer->appendResponseData('zxPicture', $structureElement->getElementData());
        }
    }
}

