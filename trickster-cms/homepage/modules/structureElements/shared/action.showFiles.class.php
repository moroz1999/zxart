<?php

class showFilesShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('form', $structureElement->getForm('files'));
            $renderer->assign('action', 'receiveFiles');
            $contentList = $structureElement->getFilesList();
            $renderer->assign('contentList', $contentList);
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
        }
    }
}