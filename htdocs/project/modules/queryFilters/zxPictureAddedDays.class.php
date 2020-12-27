<?php

class zxPictureAddedDaysQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $days = intval(reset($argument));
        } else {
            $days = intval($argument);
        }

        $date = time() - $days * 24 * 60 * 60;

        $query->whereIn(
            'id',
            function ($query) use ($date) {
                $query->select('id')
                    ->from('structure_elements')
                    ->where('structureType', '=', 'zxPicture')
                    ->where('dateCreated', '>=', $date);
            }
        );

        return $query;
    }
}