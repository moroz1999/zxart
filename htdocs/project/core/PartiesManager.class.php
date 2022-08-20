<?php

class PartiesManager extends ElementsManager
{
    const TABLE = 'module_party';
    protected $columnRelations = [];

    public function __construct()
    {
        $this->columnRelations = [
            'title' => ['LOWER(title)' => true],
            'date' => ['id' => true],
        ];
    }

    /**
     * @param languagesManager $languagesManager
     */
    public function setLanguagesManager($languagesManager)
    {
        $this->languagesManager = $languagesManager;
    }

    //SELECT * FROM `engine_module_party`
    //LEFT JOIN engine_structure_links ON (engine_structure_links.childStructureId = engine_module_party.id AND engine_structure_links.type='structure')
    //LEFT JOIN engine_module_generic ON (engine_structure_links.parentStructureId = engine_module_generic.id AND engine_structure_links.type='structure')
    //WHERE engine_module_party.title LIKE 'dihalt%' AND engine_module_generic.title = 2008
    public function getPartyByTitle($title, $year)
    {
        $partyElement = false;
        $structureManager = $this->structureManager;

        $query = $this->db->table('module_party')
            ->select('module_party.id')
            ->leftJoin(
                'structure_links',
                function ($join) {
                    $join->on('structure_links.childStructureId', '=', 'module_party.id');
                    $join->where('structure_links.type', '=', 'structure');
                }
            )
            ->leftJoin('module_generic', 'structure_links.parentStructureId', '=', 'module_generic.id')
            ->where('module_party.title', 'like', $title . '%')
            ->where('module_generic.title', '=', $year)
            ->limit(1);
        if ($record = $query->first()
        ) {
            /**
             * @var countryElement|cityElement $locationElement
             */
            $partyElement = $structureManager->getElementById($record['id']);
        }

        return $partyElement;
    }
}