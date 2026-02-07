<?php
declare(strict_types=1);


namespace ZxArt\Prods\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Helpers\AlphanumericColumnSearch;
use ZxArt\LinkTypes;

readonly final class ProdsRepository
{
    public const TABLE = 'module_zxprod';

    public function __construct(
        private Connection               $db,
        private AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {

    }

    public function findProdsByTitles(string $title): array
    {
        $theTitle = 'The ' . $title;

        $query = $this->getSelectSql();
        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $title, 'title');
        $query->orWhere('title', 'like', $title);
        $query->orWhere('title', 'like', $title . '%');

        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $title, 'altTitle');
        $query->orWhere('altTitle', 'like', $title);
        $query->orWhere('altTitle', 'like', $title . '%');

        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $theTitle, 'title');
        $query->orWhere('title', 'like', $theTitle);
        $query->orWhere('title', 'like', $theTitle . '%');

        return $query->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getNewProdIds(int $limit, float $minRating, int $daysAgo): array
    {
        $since = time() - ($daysAgo * 86400);

        return $this->getSelectSql()
            ->where('dateAdded', '>=', $since)
            ->where('votes', '>=', $minRating)
            ->orderBy('dateAdded', 'desc')
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getLatestAddedIds(int $limit): array
    {
        return $this->getSelectSql()
            ->orderBy('dateAdded', 'desc')
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getBestNewByCategoryIds(int $categoryId, int $limit, float $minRating, int $currentYear): array
    {
        return $this->getSelectSql()
            ->whereIn(self::TABLE . '.id', function ($subQuery) use ($categoryId) {
                $subQuery->from('structure_links')
                    ->select('structure_links.childStructureId')
                    ->where('structure_links.type', '=', LinkTypes::ZX_PROD_CATEGORY->value)
                    ->where('structure_links.parentStructureId', '=', $categoryId);
            })
            ->where('votes', '>=', $minRating)
            ->where('year', '>=', $currentYear - 1)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getForSaleOrDonationIds(int $limit): array
    {
        return $this->getSelectSql()
            ->whereIn('legalStatus', ['insales', 'donationware'])
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }

}
