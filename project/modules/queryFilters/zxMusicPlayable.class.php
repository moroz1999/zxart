<?php

use Illuminate\Database\Query\Builder;

class zxMusicPlayableQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        // playable: converted by the server or uploaded as a recording
        $table = $this->getTable();
        $query->where(static function (Builder $playable) use ($table): void {
            $playable->where($table . '.mp3Name', '!=', '')
                ->orWhere($table . '.audioFile', '!=', '');
        });
        return $query;
    }
}