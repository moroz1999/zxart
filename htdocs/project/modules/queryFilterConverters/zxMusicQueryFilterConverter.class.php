<?php

class zxMusicQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    protected array $fields = [
        'id',
        'partyplace',
        'title',
        'year',
        'dateAdded',
        'votes',
        'plays',
        'commentsAmount',
    ];

    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'author') {
            $query = $this->generateChildQuery($sourceData, 'module_zxmusic', 'authorMusic');
        } elseif ($sourceType == 'party') {
            $query = $this->generateChildQuery($sourceData, 'module_zxmusic', 'partyMusic');
        } elseif ($sourceType == 'game') {
            $query = $this->generateChildQuery($sourceData, 'module_zxmusic', 'gameLink');
        } elseif ($sourceType == 'tag') {
            $query = $this->generateChildQuery($sourceData, 'module_zxmusic', 'tagLink');
        } else {
            $query = $this->getService('db')->table('module_zxmusic')->select($this->fields);
        }
        return $query;
    }
}