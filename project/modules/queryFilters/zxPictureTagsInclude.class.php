<?php

class zxPictureTagsIncludeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        $tagsManager = $this->getService(tagsManager::class);
        /** @var string[] $tagNames */
        $tagNames = $argument;
        // no tagged work leaves nothing to find
        $query->whereIn($this->getTable() . '.id', $tagsManager->getConnectedElementIdsByNames($tagNames));
        return $query;
    }
}