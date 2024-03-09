<?php

class MusicManager extends ElementsManager
{
    const TABLE = 'module_zxmusic';
    protected $columnRelations = [];

    public function __construct()
    {
        $this->columnRelations = [
            'title' => ['LOWER(title)' => true],
            'place' => ['if(partyplace,0,1), partyplace' => true],
            'date' => ['dateAdded' => true, 'id' => true],
            'year' => ['year' => true, 'dateAdded' => true, 'id' => true],
            'votes' => ['votes' => true, 'if(partyplace,0,1), partyplace' => false, 'title' => true],
            'plays' => ['plays' => true, 'id' => true],
            'commentsAmount' => ['commentsAmount' => true, 'id' => true],
        ];
    }
}