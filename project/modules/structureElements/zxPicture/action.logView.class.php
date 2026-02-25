<?php

class logViewZxPicture extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->logView();
        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof RendererPluginAppendInterface) {
            $renderer->appendResponseData('zxPicture', $structureElement->getElementData());
        }
    }
}

