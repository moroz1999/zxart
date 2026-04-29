<?php

class showImportFormShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $origins = [];
            $records = [];
            $pluginsFolder = $structureManager->getElementByMarker('importPlugins');
            $importPlugins = [];
            if ($pluginsFolder) {
                $importPlugins = $pluginsFolder->getChildrenList();
            }
            if ($importPlugins) {
                foreach ($importPlugins as &$importPlugin) {
                    $origin = $importPlugin->getOriginName();
                    $origins[] = $origin;
                }
                $collection = persistableCollection::getInstance('import_origin');
                $conditions = [
                    [
                        'column' => 'importOrigin',
                        'action' => 'IN',
                        'argument' => $origins,
                    ],
                    [
                        'column' => 'elementId',
                        'action' => '=',
                        'argument' => $structureElement->id,
                    ],
                ];
                $records = $collection->conditionalLoad(['importOrigin', 'importId'], $conditions);
            }
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            //            $renderer->assign('contentSubTemplate', 'shared.importform.tpl');
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
            $renderer->assign('form', $structureElement->getForm('import'));
            $renderer->assign('action', 'receiveImportForm');
            $renderer->assign('importFormRecords', $records);
            $renderer->assign('importFormOrigins', $origins);
        }
    }
}