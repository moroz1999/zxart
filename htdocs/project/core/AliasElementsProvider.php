<?php

trait AliasElementsProvider
{
    protected $aliasElements;
    protected $aliasElementsIds;

    /**
     * @return authorAliasElement[]|groupAliasElement[]
     */
    public function getAliasElements()
    {
        if ($this->aliasElements === null) {
            $this->aliasElements = [];
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            foreach ($this->getAliasElementsIds() as $aliasId) {
                if ($aliasElement = $structureManager->getElementById($aliasId)) {
                    $this->aliasElements[] = $aliasElement;
                }
            }
        }
        return $this->aliasElements;
    }

    public function getAliasElementsIds()
    {
        if ($this->aliasElementsIds === null) {
            $this->aliasElementsIds = [];
            /**
             * @var $db \Illuminate\Database\MySqlConnection
             */
            $db = $this->getService('db');
            if ($records = $db->table('module_' . $this->structureType . 'alias')->select('id')->where(
                $this->structureType . 'Id',
                '=',
                $this->id
            )->get()) {
                $this->aliasElementsIds = array_column($records, 'id');
            }
        }
        return $this->aliasElementsIds;
    }
}