<?php

use Illuminate\Database\Query\Builder;
use ZxArt\Search\Dbal\TitleMatch;

/**
 * Tunes by a phrase in the title or the track's own title, or in the description;
 * the search of the zxnet and zxtune player APIs.
 */
class zxMusicSearchQueryFilter extends QueryFilter
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
        $phrase = (string)$argument;
        $table = $this->getTable();
        // texts are stored HTML-encoded
        $descriptionPattern = '%' . htmlspecialchars(trim($phrase), ENT_QUOTES, 'UTF-8') . '%';

        return $query->where(function (Builder $matchQuery) use ($table, $phrase, $descriptionPattern): void {
            $this->getService(TitleMatch::class)->where($matchQuery, [$table . '.title', $table . '.internalTitle'], $phrase);
            $matchQuery->orWhere($table . '.description', 'like', $descriptionPattern);
        });
    }
}
