<?php

class SearchResult
{
    public $count = 0;
    /**
     * @var SearchResultSet[]
     */
    public $sets = [];
    public $elements = [];
    public $exactMatches = true;

    public function getSearchTotal()
    {
        $allTotal = 0;
        foreach ($this->sets as $set) {
            $allTotal += $set->getTotalCount();
            if ($subCount = $set->getSubCount()){
                $allTotal += $subCount - 1;
            }
        }

        return $allTotal;
    }

}
