<?php

class zxReleaseQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    protected array $fields = [
        'id',
        'title',
        'dateCreated',
        'year',
    ];

    /**
     * @param \Illuminate\Database\Query\Builder $sourceData
     * @param string $sourceType
     * @return mixed
     */
    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxProd') {
            $query = $this->generateChildQuery($sourceData, 'module_zxrelease', 'structure', false);
        } else {
            $query = $this->getService('db')->table('module_zxrelease')->select($this->fields);
        }

        return $query;
    }
}