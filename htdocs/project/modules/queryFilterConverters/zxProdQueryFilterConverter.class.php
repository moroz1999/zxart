<?php

class zxProdQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;
    
    protected function getFields(): array
    {
        return [
            $this->getTable() . '.id',
            $this->getTable() . '.partyplace',
            $this->getTable() . '.title',
            $this->getTable() . '.year',
            $this->getTable() . '.legalStatus',
            $this->getTable() . '.dateAdded',
//            $this->getStructureTable() . '.dateCreated',
            $this->getTable() . '.votes',
        ];
    }

    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxPicture') {
            $query = $this->generateParentQuery($sourceData, $this->getTable(), 'authorPicture', false);
        } elseif ($sourceType == 'zxRelease') {
            $query = $this->generateParentQuery($sourceData, $this->getTable(), 'structure', false);
        } elseif ($sourceType == 'structure') {
            $query = $this->getService('db')
                ->table($this->getTable())
                ->whereIn($this->getTable() . '.id', $sourceData)
//                ->leftJoin($this->getStructureTable(), $this->getStructureTable() . '.id', '=', $this->getTable() . '.id')
                ->select($this->getFields());
        } else {
            $query = $this->getService('db')->table($this->getTable())
//                ->leftJoin($this->getStructureTable(), $this->getStructureTable() . '.id', '=', $this->getTable() . '.id')
                ->select($this->getFields());
        }
        return $query;
    }
}