<?php

use ZxArt\Search\Dbal\TitleMatch;

/**
 * Authors by a phrase in the nickname or the real name; the search of the zxnet player API.
 */
class authorSearchQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'author';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $argument = reset($argument);
        }
        $table = $this->getTable();

        return $this->getService(TitleMatch::class)->where($query, [$table . '.title', $table . '.realName'], (string)$argument);
    }
}
