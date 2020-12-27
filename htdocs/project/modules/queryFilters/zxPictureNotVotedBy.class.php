<?php

class zxPictureNotVotedByQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (!is_numeric($argument)) {
            $currentUser = $this->getService('user');
            $argument = $currentUser->id;
        }
        $query->whereNotIn(
            'id',
            function ($query) use ($argument) {
                if (is_array($argument)) {
                    $query->select('elementId')
                        ->from('votes_history')
                        ->where('type', '=', 'zxPicture')
                        ->whereIn('userId', $argument);
                } else {
                    $query->select('elementId')
                        ->from('votes_history')
                        ->where('type', '=', 'zxPicture')
                        ->where('userId', '=', $argument);
                }
            }
        );

        return $query;
    }
}