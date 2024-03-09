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
        } elseif ($sourceType == 'author') {
            $query = $this->getService('db')->table($this->getTable())->select($this->getFields())->distinct()->whereIn('id', function ($query) use ($sourceData) {
                $query->from('authorship')->whereIn('authorId', $sourceData->select('id'))->select('elementId');
            });
        } elseif ($sourceType == 'structure') {
            $query = $this->getService('db')
                ->table($this->getTable())
                ->whereIn($this->getTable() . '.id', $sourceData)
                ->select($this->getFields());
        } else {
            $query = $this->getService('db')->table($this->getTable())->select($this->getFields());
        }
        return $query;
    }
}