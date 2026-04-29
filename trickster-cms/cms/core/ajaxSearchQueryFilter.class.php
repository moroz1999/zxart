<?php

/**
 * This is a small extra helper class for searchQueryFilter.
 * It just disables secondary search by content fields for all ajax searches.
 */
abstract class ajaxSearchQueryFilter extends searchQueryFilter
{
    protected function getContentFieldNames()
    {
        return false;
    }
}
