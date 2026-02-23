<?php

use tagsManager;

class zxPictureTagsExcludeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        $tagsManager = $this->getService(tagsManager::class);
        if ($idList = $tagsManager->getConnectedElementIdsByNames($argument, false)) {
            $query->whereNotIn($this->getTable() . '.id', $idList);
        }
        return $query;
    }
}