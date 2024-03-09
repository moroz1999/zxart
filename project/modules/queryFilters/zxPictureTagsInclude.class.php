<?php

class zxPictureTagsIncludeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        /**
         * @var tagsManager $tagsManager
         */
        $tagsManager = $this->getService('tagsManager');
        if ($idList = $tagsManager->getConnectedElementIdsByNames($argument)) {
            $query->whereIn($this->getTable() . '.id', $idList);
        }
        return $query;
    }
}