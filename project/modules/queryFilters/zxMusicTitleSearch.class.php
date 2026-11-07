<?php

use ZxArt\Search\Dbal\TitleMatch;

class zxMusicTitleSearchQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $argument = reset($argument);
        }
        $table = $this->getTable();

        return $this->getService(TitleMatch::class)->where($query, [$table . '.title', $table . '.internalTitle'], (string)$argument);
    }
}
