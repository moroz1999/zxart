<?php

class authorQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;
    protected array $fields = [
        'id',
        'title',
        'graphicsRating',
        'musicRating',
    ];
    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxMusic') {
            $query = $this->generateParentQuery($sourceData, 'module_author', 'authorMusic', true);
        } elseif ($sourceType == 'zxPicture') {
            $query = $this->generateParentQuery($sourceData, 'module_author', 'authorPicture', true);
        } else {
            $query = $this->getService('db')->table('module_author')->select($this->fields)->distinct();
        }
        return $query;
    }
}