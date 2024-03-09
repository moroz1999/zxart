<?php

class zxReleaseQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    protected function getFields(): array
    {
        return [
            $this->getTable() . '.id',
            $this->getTable() . '.title',
//            $this->getStructureTable() . '.dateCreated',
            $this->getTable() . '.year',
        ];
    }

    /**
     * @param \Illuminate\Database\Query\Builder $sourceData
     * @param string $sourceType
     * @return mixed
     */
    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxProd') {
            $query = $this->generateChildQuery($sourceData, $this->getTable(), 'structure', false);
        } else {
            $query = $this->getService('db')->table($this->getTable())
                ->select($this->getFields());
        }

        return $query;
    }
}