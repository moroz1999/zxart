<?php

class zxMusicTagsExcludeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        /**
         * @var tagsManager $tagsManager
         */
        $tagsManager = $this->getService('tagsManager');
        if ($idList = $tagsManager->getConnectedElementIdsByNames($argument, false)) {
            $query->whereNotIn($this->getTable() . '.id', $idList);
        }
        return $query;
    }
}