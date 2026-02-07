<?php
declare(strict_types=1);


namespace ZxArt\Parties\Repositories;

use Illuminate\Database\Connection;
use ZxArt\Helpers\AlphanumericColumnSearch;

final class PartiesRepository
{
    public const TABLE = 'module_party';

    public function __construct(
        private readonly Connection               $db,
        private readonly AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {
    }

    //SELECT * FROM `engine_module_party`
    //LEFT JOIN engine_structure_links ON (engine_structure_links.childStructureId = engine_module_party.id AND engine_structure_links.type='structure')
    //LEFT JOIN engine_module_generic ON (engine_structure_links.parentStructureId = engine_module_generic.id AND engine_structure_links.type='structure')
    //WHERE engine_module_party.title LIKE 'dihalt%' AND engine_module_generic.title = 2008
    public function findPartyIdByTitleAndYear(string $title, int $year): ?int
    {

        $query = $this->db->table('module_party')
            ->select('module_party.id')
            ->limit(1)
            ->leftJoin(
                'structure_links',
                static function ($join) {
                    $join->on('structure_links.childStructureId', '=', 'module_party.id')
                        ->where('structure_links.type', '=', 'structure');
                }
            )
            ->leftJoin(
                'module_generic',
                static function ($join) use ($year) {
                    $join->on('structure_links.childStructureId', '=', 'module_generic.id')
                        ->where('module_generic.title', '=', $year);
                }
            );
        $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $title, 'engine_module_party.title');
        $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $title . ' ' . $year, 'engine_module_party.title');

        if ($record = $query->first()) {
            return $record['id'];
        }

        return null;
    }

    /**
     * @return int[]
     */
    public function getRecentIds(int $limit): array
    {
        return $this->db->table(self::TABLE . ' AS parties')
            ->select('parties.id')
            ->leftJoin('structure_elements AS partystruct', 'partystruct.id', '=', 'parties.id')
            ->leftJoin(
                'structure_links AS links',
                static function ($join) {
                    $join->on('links.childStructureId', '=', 'parties.id')
                        ->where('links.type', '=', 'structure');
                }
            )
            ->leftJoin('structure_elements AS el2', 'el2.id', '=', 'links.parentStructureId')
            ->orderBy('el2.structureName', 'desc')
            ->orderBy('partystruct.dateCreated', 'desc')
            ->limit($limit)
            ->pluck('parties.id');
    }
}
