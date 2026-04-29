<?php

class DBValueSetDataChunk extends DataChunk implements ElementHolderInterface, ExtraDataHolderDataChunkInterface
{
    use ElementHolderDataChunkTrait;
    protected $rows;
    protected $idField = 'id';
    protected $elementIdField = 'elementId';
    protected $valueField = 'value';
    protected $tableName;

    public function getStorageValue()
    {
        if ($this->storageValue === null) {
            $this->loadStorageValue();
        }
        return $this->storageValue;
    }

    public function setExternalValue($value)
    {
        $this->formValue = null;
        $this->storageValue = (array)$value;
    }

    protected function loadStorageValue()
    {
        $this->storageValue = [];

        if ($rows = $this->getRows()) {
            $this->storageValue = array_column($rows, $this->valueField);
        }
    }

    protected function getRows()
    {
        if ($this->rows === null) {
            $this->rows = [];
            if ($db = $this->getService('db')) {
                if ($this->rows = $db->table($this->tableName)
                    ->where($this->elementIdField, '=', $this->structureElement->id)
                    ->select($this->valueField, $this->idField)->get()) {
                    return $this->rows;
                }
            }
        }
        return $this->rows;
    }

    public function convertFormToStorage()
    {
        //if form was empty, then we still need an array in storage value, otherwise it will not be saved to db.
        $this->storageValue = (array)$this->formValue;
        $this->displayValue = $this->storageValue;
    }

    public function convertStorageToDisplay()
    {
        if ($this->storageValue === null) {
            $this->loadStorageValue();
        }
        $this->displayValue = $this->storageValue;
    }

    public function convertStorageToForm()
    {
        if ($this->storageValue === null) {
            $this->loadStorageValue();
        }
        $this->formValue = $this->storageValue;
    }

    public function persistExtraData()
    {
        if ($this->storageValue === null) {
            //this chunk wasn't modified at all, no need to load it and save it again.
            return;
        }
        if ($db = $this->getService('db')) {
            $valuesToInsert = $this->storageValue;
            $rowsToDelete = $this->getRows();
            foreach ($valuesToInsert as $keyInsert => $value) {
                if ($value) {
                    foreach ($rowsToDelete as $keyDelete => $row) {
                        if ($row[$this->valueField] == $value) {
                            unset($valuesToInsert[$keyInsert]);
                            unset($rowsToDelete[$keyDelete]);
                            break;
                        }
                    }
                } else {
                    unset($valuesToInsert[$keyInsert]);
                }
            }
            $insertData = [];
            $elementId = $this->structureElement->id;

            foreach ($valuesToInsert as $value) {
                $insertData[] = [$this->elementIdField => $elementId, $this->valueField => $value];
            }
            if ($insertData) {
                $db->table($this->tableName)->insert($insertData);
            }

            if ($deleteIds = array_column($rowsToDelete, $this->idField)) {
                $db->table($this->tableName)->whereIn($this->idField, $deleteIds)->delete();
            }
        }
        //if we save it two times in a row, then we need to empty the rows
        $this->rows = null;
    }

    public function deleteExtraData()
    {
        if ($db = $this->getService('db')) {
            if ($deleteIds = array_column($this->getRows(), $this->idField)) {
                $db->table($this->tableName)->whereIn($this->idField, $deleteIds)->delete();
            }
        }
    }

    public function copyExtraData($oldValue, $oldId, $newId)
    {
        if ($db = $this->getService('db')) {
            $insertData = [];
            foreach ($this->getRows() as $value) {
                $insertData[] = [$this->elementIdField => $newId, $this->valueField => $value];
            }
            if ($insertData) {
                $db->table($this->tableName)->insert($insertData);
            }
        }
    }
}