<?php

class zxPictureQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    protected function getFields(): array
    {
        return [
            'id',
            'partyplace',
            'title',
            'year',
            'dateAdded',
            'votes',
            'views',
            'commentsAmount',
        ];
    }
    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'author') {
            $query = $this->generateChildQuery($sourceData, 'module_zxpicture', 'authorPicture');
        } elseif ($sourceType == 'party') {
            $query = $this->generateChildQuery($sourceData, 'module_zxpicture', 'partyPicture');
        } elseif ($sourceType == 'zxProd') {
            $query = $this->generateChildQuery($sourceData, 'module_zxpicture', 'gameLink');
        } elseif ($sourceType == 'tag') {
            $query = $this->generateChildQuery($sourceData, 'module_zxpicture', 'tagLink');
        } else {
            $query = $this->getService('db')->table('module_zxpicture')->select($this->getFields());
        }
        return $query;
    }
}