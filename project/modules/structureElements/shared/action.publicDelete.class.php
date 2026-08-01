<?php

class publicDeleteShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $renderer = $this->getService(renderer::class);
        $respondsWithJson = $renderer instanceof RendererPluginAppendInterface;

        // The parent has to be resolved while the element still exists.
        $redirectURL = false;
        if (!$respondsWithJson) {
            $parentElement = $structureManager->getElementsFirstParent($structureElement->getId());
            $redirectURL = $parentElement->URL;
        }

        $structureElement->deleteElementData();

        if ($respondsWithJson) {
            // SPA (/ajax/) path — the client owns the route to go to next
            $renderer->assign('body', ['success' => true]);
            return;
        }

        if ($redirectURL) {
            $controller->redirect($redirectURL);
        }
    }
}
