<?php

class authorAliasOfItemTypeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'authorAlias';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (!is_array($argument)) {
            $argument = (array)$argument;
        }
        $graphics = false;
        $music = false;
        foreach ($argument as $type) {
            if ($type == 'authorPicture') {
                $graphics = true;
            } elseif ($type == 'authorMusic') {
                $music = true;
            }
        }

        if ($graphics && !$music) {
            $query->where($this->getTable() . '.displayInGraphics', '=', 1);
        } elseif (!$graphics && $music) {
            $query->where($this->getTable() . '.displayInMusic', '=', 1);
        }

        return $query;
    }
}