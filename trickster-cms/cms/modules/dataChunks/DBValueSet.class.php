<?php

class DBValueSetDataChunk extends DataChunk implements ElementHolderInterface, ExtraDataHolderDataChunkInterface
{
    use ElementHolderDataChunkTrait;
    protected $rows;
    protected $idField = 'id';
    protected $elementIdField = 'elementId';
    protected $valueField = 'value';
    protected $tableName;

    /**
     * Optional code lookup. When `lookupTable` is configured the link table
     * stores numeric ids in `valueField`, while the element property keeps
     * working in codes: ids are translated to codes on read and back on write.
     * Used by hardware, whose catalog is an editable table.
     */
    protected $lookupTable;
    protected $lookupIdField = 'id';
    protected $lookupCodeField = 'code';

    /**
     * Lookup maps shared by every chunk instance of a request, keyed by lookup
     * table name — several element types point at the same lookup table and the
     * map is small enough to read once.
     *
     * @var array<string, array<int, string>>
     */
    protected static $lookupCodesById = [];

    /**
     * @var array<string, array<string, int>>
     */
    protected static $lookupIdsByCode = [];

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
            $this->storageValue = $this->convertStoredToCodes(array_column($rows, $this->valueField));
        }
    }

    protected function hasLookup()
    {
        return $this->lookupTable !== null;
    }

    /**
     * Reads the lookup table once per request, filling both directions at the
     * same time — a chunk needs one on read and the other on write.
     */
    protected function loadLookupMaps()
    {
        if (isset(self::$lookupCodesById[$this->lookupTable])) {
            return;
        }

        $codesById = [];
        $idsByCode = [];
        if ($db = $this->getService('db')) {
            $rows = $db->table($this->lookupTable)
                ->select($this->lookupIdField, $this->lookupCodeField)
                ->get();
            foreach ($rows as $row) {
                $id = (int)$row[$this->lookupIdField];
                $code = (string)$row[$this->lookupCodeField];
                $codesById[$id] = $code;
                $idsByCode[$code] = $id;
            }
        }

        self::$lookupCodesById[$this->lookupTable] = $codesById;
        self::$lookupIdsByCode[$this->lookupTable] = $idsByCode;
    }

    /**
     * Stored values to the codes the element exposes. Unknown ids are dropped:
     * they can only come from a row whose catalog entry is gone.
     */
    protected function convertStoredToCodes(array $storedValues)
    {
        if (!$this->hasLookup()) {
            return $storedValues;
        }
        $this->loadLookupMaps();
        $codeById = self::$lookupCodesById[$this->lookupTable];
        $codes = [];
        foreach ($storedValues as $storedValue) {
            $id = (int)$storedValue;
            if (isset($codeById[$id])) {
                $codes[] = $codeById[$id];
            }
        }
        return $codes;
    }

    /**
     * Codes back to the values the link table stores. Unknown codes are dropped
     * rather than written as 0, so a stale code can never create a broken row.
     */
    protected function convertCodesToStored(array $codes)
    {
        if (!$this->hasLookup()) {
            return $codes;
        }
        $this->loadLookupMaps();
        $idByCode = self::$lookupIdsByCode[$this->lookupTable];
        $storedValues = [];
        foreach ($codes as $code) {
            if (isset($idByCode[$code])) {
                $storedValues[] = $idByCode[$code];
            }
        }
        return $storedValues;
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
            // must happen before the diff below: getRows() yields stored values,
            // so both sides have to be in the same representation
            $valuesToInsert = $this->convertCodesToStored($this->storageValue);
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