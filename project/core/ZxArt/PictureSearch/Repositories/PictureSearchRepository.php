<?php

declare(strict_types=1);

namespace ZxArt\PictureSearch\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use tagsManager;
use ZxArt\PictureSearch\Dto\PictureSearchQuery;

/**
 * Builds the graphics-search SQL directly against the picture, author and
 * structure-link tables. Filtering lives here; ordering, pagination and element
 * loading are applied by the caller on the returned query.
 */
readonly final class PictureSearchRepository
{
    private const string PICTURES_TABLE = 'module_zxpicture';
    private const string AUTHORS_TABLE = 'module_author';
    private const string LINKS_TABLE = 'structure_links';
    private const string AUTHOR_PICTURE_LINK = 'authorPicture';
    private const int MIN_YEAR = 1970;

    public function __construct(
        private Connection $db,
        private tagsManager $tagsManager,
        private LanguagesManager $languagesManager,
    ) {
    }

    /**
     * Query over pictures matching the content and author-location filters.
     */
    public function buildPicturesQuery(PictureSearchQuery $query): Builder
    {
        $builder = $this->db->table(self::PICTURES_TABLE);
        $this->applyContentFilters($builder, $query);
        $this->applyAuthorLocation($builder, $query->authorCountryIds, $query->authorCityIds);
        return $builder;
    }

    /**
     * Query over graphics authors, optionally narrowed to authors of pictures
     * that match the content filters and to the given locations.
     */
    public function buildAuthorsQuery(PictureSearchQuery $query): Builder
    {
        $builder = $this->db->table(self::AUTHORS_TABLE)
            ->where('languageId', '=', $this->getCurrentLanguageId())
            ->where('displayInGraphics', '=', 1);
        if ($query->authorCountryIds !== []) {
            $builder->whereIn('country', $query->authorCountryIds);
        }
        if ($query->authorCityIds !== []) {
            $builder->whereIn('city', $query->authorCityIds);
        }
        if ($this->hasContentFilters($query)) {
            $contentQuery = $this->db->table(self::PICTURES_TABLE)->select('id');
            $this->applyContentFilters($contentQuery, $query);
            $builder->whereIn('id', function (Builder $sub) use ($contentQuery) {
                $sub->select(self::LINKS_TABLE . '.parentStructureId')
                    ->from(self::LINKS_TABLE)
                    ->where(self::LINKS_TABLE . '.type', '=', self::AUTHOR_PICTURE_LINK)
                    ->whereIn(self::LINKS_TABLE . '.childStructureId', $contentQuery);
            });
        }
        return $builder;
    }

    private function applyContentFilters(Builder $builder, PictureSearchQuery $query): void
    {
        if ($query->titleWord !== null) {
            $builder->where('title', 'like', '%' . $query->titleWord . '%');
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
        if ($query->pictureType !== null) {
            $builder->where('type', '=', $query->pictureType);
        }
        if ($query->realtimeOnly) {
            $builder->whereIn('compo', ['realtime', 'realtimep']);
        }
        if ($query->inspirationOnly) {
            $builder->where('inspiredName', '!=', '');
        }
        if ($query->stagesOnly) {
            $builder->where('sequenceName', '!=', '');
        }
        if ($query->fromGameOnly) {
            $builder->where('game', '!=', '0')->where('game', '!=', '');
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
                ->where(self::LINKS_TABLE . '.type', '=', self::AUTHOR_PICTURE_LINK)
                ->whereIn(self::LINKS_TABLE . '.parentStructureId', function (Builder $authorSub) use ($countryIds, $cityIds) {
                    $authorSub->select('id')
                        ->from(self::AUTHORS_TABLE)
                        ->where('languageId', '=', $this->getCurrentLanguageId());
                    if ($countryIds !== []) {
                        $authorSub->whereIn('country', $countryIds);
                    }
                    if ($cityIds !== []) {
                        $authorSub->whereIn('city', $cityIds);
                    }
                });
        });
    }

    private function getCurrentLanguageId(): int
    {
        return (int)$this->languagesManager->getCurrentLanguageId();
    }

    private function hasContentFilters(PictureSearchQuery $query): bool
    {
        return $query->titleWord !== null
            || $query->startYear !== null
            || $query->endYear !== null
            || $query->minRating !== null
            || $query->minPartyPlace !== null
            || $query->pictureType !== null
            || $query->realtimeOnly
            || $query->inspirationOnly
            || $query->stagesOnly
            || $query->fromGameOnly
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
