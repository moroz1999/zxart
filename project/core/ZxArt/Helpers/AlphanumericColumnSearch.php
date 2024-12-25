<?php
declare(strict_types=1);

namespace ZxArt\Helpers;

use Illuminate\Database\Query\Builder;

final class AlphanumericColumnSearch
{
    public function addSearchByTitle(Builder $query, ?string $searchTerm, $propertyName): Builder
    {
        if ($searchTerm === null) {
            return $query;
        }
        $alphanumericTerm = $this->toAlphanumeric($searchTerm);
        // trim, decode some html, convert to alphanumeric without spaces
        $query->orWhereRaw("
         REGEXP_REPLACE(
            LOWER(
                REPLACE(
                   REPLACE(
                       REPLACE(TRIM({$propertyName}), '&quot;', '\"'),
                       '&amp;', '&'
                   ),
                   '&lt;', '<'
               )
            ), '[^a-z0-9а-я]', ''
         ) = ?", [$alphanumericTerm]);

        return $query;
    }

    public function toAlphanumeric(string $input): string
    {
        return preg_replace('/[^\p{L}\p{N}]/u', '', mb_strtolower(trim($input)));
    }
}