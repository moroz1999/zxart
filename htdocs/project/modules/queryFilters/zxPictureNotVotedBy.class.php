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
                    $query->select('votes_history.elementId')
                        ->from('votes_history')
                        ->where('votes_history.type', '=', 'zxPicture')
                        ->whereIn('votes_history.userId', $argument);
                } else {
                    $query->select('votes_history.elementId')
                        ->from('votes_history')
                        ->where('votes_history.type', '=', 'zxPicture')
                        ->where('votes_history.userId', '=', $argument);
                }
            }
        );

        return $query;
    }
}