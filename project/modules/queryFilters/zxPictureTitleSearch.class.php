<?php

use ZxArt\Search\Dbal\TitleMatch;

class zxPictureTitleSearchQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $argument = reset($argument);
        }

        return $this->getService(TitleMatch::class)->where($query, [$this->getTable() . '.title'], (string)$argument);
    }
}
