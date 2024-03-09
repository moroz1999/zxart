<?php

class partyQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    protected function getFields(): array
    {
        return [
            'id',
            'title',
        ];
    }
    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxMusic') {
            $query = $this->generateParentQuery($sourceData, 'module_party', 'partyMusic');
        } elseif ($sourceType == 'zxPicture') {
            $query = $this->generateParentQuery($sourceData, 'module_party', 'partyPicture');
        } else {
            $query = $this->getService('db')->table('module_party')->select($this->getFields());
        }
        return $query;
    }

}