<?php

use App\Users\CurrentUserService;

class zxMusicNotVotedByQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (!is_numeric($argument)) {
            $currentUserService = $this->getService(CurrentUserService::class);
            $currentUser = $currentUserService->getCurrentUser();
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



