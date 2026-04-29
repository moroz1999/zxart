<?php

use Illuminate\Database\Connection;

class IconsManager
{
    /**
     * @var genericIconElement[]
     */
    protected $iconElements;

    public function __construct(
        protected structureManager $structureManager,
        protected Connection $db,
    )
    {
    }

    public function getElementIcons($id)
    {
    }

    public function getAllIcons()
    {
        if ($this->iconElements === null) {
            $this->iconElements = [];
            $allIconsIds = $this->db->table('module_generic_icon')->select('module_generic_icon.id')
                ->leftJoin('structure_links', function ($query) {
                    $query->on('module_generic_icon.id', '=', 'structure_links.childStructureId')
                        ->where('structure_links.type', '=', 'structure');
                })
                ->distinct()
                ->orderBy('structure_links.position', 'asc')
                ->get();
            $allIconsIds = array_column($allIconsIds, 'id');
            if ($iconElements = $this->structureManager->getElementsByIdList($allIconsIds, null, true)) {
                $this->iconElements = $iconElements;
            }
        }
        return $this->iconElements;
    }
}