<?php

class logViewZxPicture extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->logView();
        $renderer = $this->getService('renderer');
        if ($renderer instanceof rendererPluginAppendInterface) {
            $renderer->appendResponseData('zxPicture', $structureElement->getElementData());
        }
    }
}

