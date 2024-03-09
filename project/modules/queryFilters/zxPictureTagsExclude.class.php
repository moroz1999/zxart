<?php

class zxPictureTagsExcludeQueryFilter extends QueryFilter
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
        if ($idList = $tagsManager->getConnectedElementIdsByNames($argument, false)) {
            $query->whereNotIn($this->getTable() . '.id', $idList);
        }
        return $query;
    }
}