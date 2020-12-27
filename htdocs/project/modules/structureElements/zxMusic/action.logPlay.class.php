<?php

class logPlayZxMusic extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->logPlay();

        $renderer = $this->getService('renderer');
        if ($renderer instanceof rendererPluginAppendInterface) {
            $renderer->appendResponseData('zxMusic', $structureElement->getElementData());
        }
    }
}

