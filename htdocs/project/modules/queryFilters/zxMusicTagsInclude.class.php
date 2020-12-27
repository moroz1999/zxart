<?php

class zxMusicTagsIncludeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $tagsManager = $this->getService('tagsManager');
        if ($idList = $tagsManager->getConnectedElementIdsByNames($argument)) {
            $query->whereIn('id', $idList);
        }
        return $query;
    }
}