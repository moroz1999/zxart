<?php

class receiveImportFormShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $collection = persistableCollection::getInstance('import_origin');
            $existingRecords = $collection->load([
                'elementId' => $structureElement->id,
            ]);
            $existingOriginRecords = [];
            foreach ($existingRecords as $record) {
                if (!isset($existingOriginRecords[$record->importOrigin])) {
                    $existingOriginRecords[$record->importOrigin] = [];
                }
                $existingOriginRecords[$record->importOrigin][$record->importId] = $record;
            }
            $relevantRecordsIds = [];
            foreach ($structureElement->importInfo as &$info) {
                $importId = trim($info['importId']);
                $origin = $info['importOrigin'];
                if (!$importId) {
                    continue;
                }

                if (isset($existingOriginRecords[$origin]) && isset($existingOriginRecords[$origin][$importId])) {
                    $recordId = $existingOriginRecords[$origin][$importId]->id;
                    $relevantRecordsIds[$recordId] = true;
                } else {
                    $originEntry = $collection->getEmptyObject();
                    $originEntry->importOrigin = $origin;
                    $originEntry->importId = $importId;
                    $originEntry->elementId = $structureElement->id;
                    $originEntry->persist();
                }
            }
            foreach ($existingRecords as $record) {
                if (!isset($relevantRecordsIds[$record->id])) {
                    $record->delete();
                }
            }
            $controller->redirect($structureElement->URL . 'id:' . $structureElement->id . '/action:showImportForm/');
        }
        $structureElement->executeAction('showImportForm');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'importInfo',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}

