<?php

use App\Users\CurrentUser;

class zxMusicNotVotedByQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (!is_numeric($argument)) {
            $currentUser = $this->getService(CurrentUser::class);
            $argument = [$currentUser->id];
        }
        $query->whereNotIn(
            $this->getTable() . '.id',
            function ($query) use ($argument) {
                $query->select('votes_history.elementId')
                    ->from('votes_history')
                    ->where('votes_history.type', '=', 'zxMusic')
                    ->whereIn('votes_history.userId', $argument);
            }
        );

        return $query;
    }
}