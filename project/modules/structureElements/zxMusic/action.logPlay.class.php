<?php

class logPlayZxMusic extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->logPlay();

        $renderer = $this->getService(renderer::class);
        if ($renderer instanceof RendererPluginAppendInterface) {
            $renderer->appendResponseData('zxMusic', $structureElement->getElementData());
        }
    }
}

