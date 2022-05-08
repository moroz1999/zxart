<?php

class authorQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    protected function getFields(): array
    {
        return [
            'id',
            'title',
            'graphicsRating',
            'musicRating',
        ];
    }

    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxMusic') {
            $query = $this->generateParentQuery($sourceData, 'module_author', 'authorMusic', true);
        } elseif ($sourceType == 'zxPicture') {
            $query = $this->generateParentQuery($sourceData, 'module_author', 'authorPicture', true);
        } elseif ($sourceType == 'zxProd') {
            $query = $this->getService('db')->table('module_author')->select($this->getFields())->distinct()->whereIn('id', function ($query) use ($sourceData) {
                $query->from('authorship')->whereIn('elementId', $sourceData->select('id'))->select('authorId');
            });
        } else {
            $query = $this->getService('db')->table('module_author')->select($this->getFields())->distinct();
        }
        return $query;
    }
}