<?php

declare(strict_types=1);

namespace ZxArt\ZxProdCategories\Repositories;

use Illuminate\Database\Connection;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;
use ZxArt\Shared\StructureType;

/**
 * Reads the whole category tree in three queries.
 *
 * The management screen shows every category with its title in every language,
 * and walking ~285 elements through the structure manager would load each one
 * of them; the tree itself is nothing but rows and links.
 */
final readonly class CategoryManageRepository extends AbstractRepository
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return array<int, array<int, string>> categoryId => languageId => title
     */
    public function getTitles(): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::ZxProdCategory))
            ->select(['id', 'languageId', 'title'])
            ->get();

        $titles = [];
        foreach ($rows as $row) {
            $titles[(int)$row['id']][(int)$row['languageId']] = (string)$row['title'];
        }

        return $titles;
    }

    /**
     * Parent-child links of the tree, in the order the CMS displays them.
     *
     * @return list<array{parentId: int, categoryId: int}>
     */
    public function getTreeLinks(): array
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $elementsTable = $this->tableName(DatabaseTable::StructureElements);

        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table($linksTable)
            ->select([$linksTable . '.parentStructureId', $linksTable . '.childStructureId'])
            ->join($elementsTable, $elementsTable . '.id', '=', $linksTable . '.childStructureId')
            ->where($linksTable . '.type', '=', LinkTypes::STRUCTURE->value)
            ->where($elementsTable . '.structureType', '=', StructureType::ZxProdCategory->value)
            ->orderBy($linksTable . '.position')
            ->get();

        $links = [];
        foreach ($rows as $row) {
            $links[] = [
                'parentId' => (int)$row['parentStructureId'],
                'categoryId' => (int)$row['childStructureId'],
            ];
        }

        return $links;
    }

    /**
     * How many productions are filed under each category, its own link only.
     *
     * @return array<int, int> categoryId => count
     */
    public function getProdCounts(): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->select('parentStructureId')
            ->selectRaw('COUNT(*) AS amount')
            ->where('type', '=', LinkTypes::ZX_PROD_CATEGORY->value)
            ->groupBy('parentStructureId')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int)$row['parentStructureId']] = (int)$row['amount'];
        }

        return $counts;
    }
}
