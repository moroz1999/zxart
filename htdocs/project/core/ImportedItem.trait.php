<?php

trait ImportedItemTrait
{
    protected $linksInfo;

    protected function deleteImportOriginRows()
    {
        $db = $this->getService('db');
        return $db->table('import_origin')->where('elementId', '=', $this->id)->delete();
    }

    public function getImportOriginId($origin)
    {
        $db = $this->getService('db');
        return $db->table('import_origin')
            ->where('elementId', '=', $this->id)->where('importOrigin', '=', $origin)->limit(1)->value('importId');
    }

    public function getImportIdsIndex()
    {
        $result = [];
        foreach ($this->getLinksInfo() as $linkInfo) {
            $result[$linkInfo['type']] = $linkInfo['id'];
        }
        return $result;
    }

    public function deleteElementData()
    {
        $this->deleteImportOriginRows();
        parent::deleteElementData();
    }
}