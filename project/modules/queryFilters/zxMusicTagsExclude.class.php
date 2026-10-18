<?php

class zxMusicTagsExcludeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $tagsManager = $this->getService(tagsManager::class);
        /** @var string[] $tagNames */
        $tagNames = $argument;
        if ($idList = $tagsManager->getConnectedElementIdsByNames($tagNames, false)) {
            $query->whereNotIn($this->getTable() . '.id', $idList);
        }
        return $query;
    }
}