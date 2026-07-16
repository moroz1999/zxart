<?php

declare(strict_types=1);

namespace ZxArt\MusicSearch\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use tagsManager;
use ZxArt\MusicSearch\Dto\MusicSearchQuery;

/**
 * Builds the music-search SQL directly against the music, author and
 * structure-link tables. Filtering lives here; ordering, pagination and element
 * loading are applied by the caller on the returned query.
 */
readonly final class MusicSearchRepository
{
    private const string MUSIC_TABLE = 'module_zxmusic';
    private const string AUTHORS_TABLE = 'module_author';
    private const string LINKS_TABLE = 'structure_links';
    private const string AUTHOR_MUSIC_LINK = 'authorMusic';
    private const int MIN_YEAR = 1970;

    /** @var string[] */
    private const array REALTIME_COMPO = ['realtime', 'realtimeay', 'realtimebeeper'];

    public function __construct(
        private Connection $db,
        private tagsManager $tagsManager,
    ) {
    }

    /**
     * Query over tunes matching the content and author-location filters.
     */
    public function buildTunesQuery(MusicSearchQuery $query): Builder
    {
        $builder = $this->db->table(self::MUSIC_TABLE);
        $this->applyContentFilters($builder, $query);
        $this->applyAuthorLocation($builder, $query->authorCountryIds, $query->authorCityIds);
        return $builder;
    }

    /**
     * Query over music authors, optionally narrowed to authors of tunes that
     * match the content filters and to the given locations.
     */
    public function buildAuthorsQuery(MusicSearchQuery $query): Builder
    {
        $builder = $this->db->table(self::AUTHORS_TABLE)->where('displayInMusic', '=', 1);
        if ($query->authorCountryIds !== []) {
            $builder->whereIn('country', $query->authorCountryIds);
        }
        if ($query->authorCityIds !== []) {
            $builder->whereIn('city', $query->authorCityIds);
        }
        if ($this->hasContentFilters($query)) {
            $contentQuery = $this->db->table(self::MUSIC_TABLE)->select('id');
            $this->applyContentFilters($contentQuery, $query);
            $builder->whereIn('id', function (Builder $sub) use ($contentQuery) {
                $sub->select(self::LINKS_TABLE . '.parentStructureId')
                    ->from(self::LINKS_TABLE)
                    ->where(self::LINKS_TABLE . '.type', '=', self::AUTHOR_MUSIC_LINK)
                    ->whereIn(self::LINKS_TABLE . '.childStructureId', $contentQuery);
            });
        }
        return $builder;
    }

    private function applyContentFilters(Builder $builder, MusicSearchQuery $query): void
    {
        if ($query->titleWord !== null) {
            $builder->where(function (Builder $sub) use ($query) {
                $sub->where('title', 'like', '%' . $query->titleWord . '%')
                    ->orWhere('internalTitle', 'like', '%' . $query->titleWord . '%');
            });
        }
        $years = $this->buildYearRange($query->startYear, $query->endYear);
        if ($years !== []) {
            $builder->whereIn('year', $years);
        }
        if ($query->minPartyPlace !== null) {
            $builder->where('partyplace', '<=', $query->minPartyPlace)->where('partyplace', '!=', 0);
        }
        if ($query->minRating !== null) {
            $builder->where('votes', '>=', $query->minRating);
        }
        if ($query->formatGroup !== null) {
            $builder->where('formatGroup', '=', $query->formatGroup);
        }
        if ($query->format !== null) {
            $builder->where('type', '=', $query->format);
        }
        if ($query->realtimeOnly) {
            $builder->whereIn('compo', self::REALTIME_COMPO);
        }
        if ($query->tagsInclude !== []) {
            /** @var int[] $ids */
            $ids = $this->tagsManager->getConnectedElementIdsByNames($query->tagsInclude);
            if ($ids) {
                $builder->whereIn('id', $ids);
            }
        }
        if ($query->tagsExclude !== []) {
            /** @var int[] $ids */
            $ids = $this->tagsManager->getConnectedElementIdsByNames($query->tagsExclude, false);
            if ($ids) {
                $builder->whereNotIn('id', $ids);
            }
        }
    }

    /**
     * @param int[] $countryIds
     * @param int[] $cityIds
     */
    private function applyAuthorLocation(Builder $builder, array $countryIds, array $cityIds): void
    {
        if ($countryIds === [] && $cityIds === []) {
            return;
        }
        $builder->whereIn('id', function (Builder $sub) use ($countryIds, $cityIds) {
            $sub->select(self::LINKS_TABLE . '.childStructureId')
                ->from(self::LINKS_TABLE)
                ->where(self::LINKS_TABLE . '.type', '=', self::AUTHOR_MUSIC_LINK)
                ->whereIn(self::LINKS_TABLE . '.parentStructureId', function (Builder $authorSub) use ($countryIds, $cityIds) {
                    $authorSub->select('id')->from(self::AUTHORS_TABLE);
                    if ($countryIds !== []) {
                        $authorSub->whereIn('country', $countryIds);
                    }
                    if ($cityIds !== []) {
                        $authorSub->whereIn('city', $cityIds);
                    }
                });
        });
    }

    private function hasContentFilters(MusicSearchQuery $query): bool
    {
        return $query->titleWord !== null
            || $query->startYear !== null
            || $query->endYear !== null
            || $query->minRating !== null
            || $query->minPartyPlace !== null
            || $query->formatGroup !== null
            || $query->format !== null
            || $query->realtimeOnly
            || $query->tagsInclude !== []
            || $query->tagsExclude !== [];
    }

    /**
     * @return int[]
     */
    private function buildYearRange(?int $startYear, ?int $endYear): array
    {
        $start = $startYear ?? 0;
        $end = $endYear ?? 0;
        if ($start > 0 && $end === 0) {
            $end = (int)date('Y');
        }
        if ($end > 0 && $start === 0) {
            $start = self::MIN_YEAR;
        }
        if ($start <= 0 || $end <= 0) {
            return [];
        }
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }
        return range($start, $end);
    }
}
