<?php

use ZxArt\Import\ImportOrigin;

trait ImportedItemTrait
{
    protected $linksInfo;

    protected function deleteImportOriginRows()
    {
        $db = $this->getService('db');
        return $db->table('import_origin')->where('elementId', '=', $this->id)->delete();
    }

    public function getImportOriginId(ImportOrigin $origin): ?string
    {
        $db = $this->getService('db');
        return $db->table('import_origin')
            ->where('elementId', '=', $this->id)->where('importOrigin', '=', $origin->value)->limit(1)->value('importId');
    }

    public function getImportIdsIndex(): array
    {
        $result = [];
        foreach ($this->getLinksInfo() as $linkInfo) {
            $result[$linkInfo['type']] = $linkInfo['id'];
        }
        return $result;
    }

    /**
     * @return void
     */
    public function deleteElementData()
    {
        $this->deleteImportOriginRows();
        parent::deleteElementData();
    }
}