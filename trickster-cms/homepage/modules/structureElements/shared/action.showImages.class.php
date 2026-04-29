<?php

class showImagesShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            if ($structureElement->hasActualStructureInfo()) {
                $structureElement->newForm = $structureManager->createElement('galleryImage', 'showForm', $structureElement->id);
            }
            $form = $structureElement->getForm('images');
            $form->setFormAction($structureElement->newForm->URL);

            $contentList = $structureElement->getImagesList();

            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
            $renderer->assign('linkType', 'structure');
            $renderer->assign('contentList', $contentList);
            $renderer->assign('form', $form);
        }
    }
}