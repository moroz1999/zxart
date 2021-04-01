<?php

class zxProdQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;
    protected array $fields = [
        'id',
        'partyplace',
        'title',
        'year',
        'dateAdded',
        'votes',
    ];
    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxPicture') {
            $query = $this->generateParentQuery($sourceData, 'module_zxprod', 'authorPicture', false);
        } elseif ($sourceType == 'zxRelease') {
            $query = $this->generateParentQuery($sourceData, 'module_zxprod', 'structure', false);
        } elseif ($sourceType == 'structure') {
            $query = $this->getService('db')
                ->table('module_zxprod')
                ->whereIn('id', $sourceData)
                ->select($this->fields);
        } else {
            $query = $this->getService('db')->table('module_zxprod')->select($this->fields);
        }
        return $query;
    }
}