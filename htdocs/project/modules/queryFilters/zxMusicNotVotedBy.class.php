<?php

class zxMusicNotVotedByQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (!is_numeric($argument)) {
            $currentUser = $this->getService('user');
            $argument = [$currentUser->id];
        }
        $query->whereNotIn(
            'id',
            function ($query) use ($argument) {
                $query->select('elementId')
                    ->from('votes_history')
                    ->where('type', '=', 'zxMusic')
                    ->whereIn('userId', $argument);
            }
        );

        return $query;
    }
}