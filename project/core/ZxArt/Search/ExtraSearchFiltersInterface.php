<?php

namespace ZxArt\Search;

use Illuminate\Database\Query\Builder;

interface ExtraSearchFiltersInterface
{
    public function assignExtraFilters(Builder $query): Builder;
}