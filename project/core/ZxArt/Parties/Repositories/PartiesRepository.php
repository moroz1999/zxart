<?php
declare(strict_types=1);


namespace ZxArt\Parties\Repositories;

use Illuminate\Database\Connection;

final class PartiesRepository
{
    public const TABLE = 'module_party';

    public function __construct(
        private readonly Connection $db,
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
            ->where('module_party.title', 'like', $title . '%')
            ->limit(1)
            ->leftJoin(
                'structure_links',
                static function ($join) {
                    $join->on('structure_links.childStructureId', '=', 'module_party.id');
                    $join->where('structure_links.type', '=', 'structure');
                }
            )->leftJoin('module_generic', 'structure_links.parentStructureId', '=', 'module_generic.id')
            ->where('module_generic.title', '=', $year);


        if ($record = $query->first()) {
            return $record['id'];
        }

        return null;
    }
}
