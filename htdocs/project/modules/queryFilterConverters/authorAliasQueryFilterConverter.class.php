<?php

class authorAliasQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    protected string $table = 'module_authoralias';

    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxMusic') {
            $query = $this->generateParentQuery($sourceData, 'module_authoralias', 'authorMusic');
        } elseif ($sourceType == 'zxPicture') {
            $query = $this->generateParentQuery($sourceData, 'module_authoralias', 'authorPicture');
        } else {
            $query = $this->getService('db')->table('module_authoralias')->select($this->getFields());
        }
        return $query;
    }

    protected function getTable(): string
    {
        return 'module_authoralias';
    }
}
