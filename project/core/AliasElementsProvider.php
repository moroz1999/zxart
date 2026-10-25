<?php

trait AliasElementsProvider
{
    protected $aliasElements;
    protected $aliasElementsIds;

    /**
     * Makes the aliases be read again: aliases added or moved since they were
     * first read are not seen otherwise.
     */
    public function resetAliasElements(): void
    {
        $this->aliasElements = null;
        $this->aliasElementsIds = null;
    }

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
                if ($aliasElement = $structureManager->getElementById((int)$aliasId)) {
                    $this->aliasElements[] = $aliasElement;
                }
            }
        }
        return $this->aliasElements;
    }

    /**
     * @return array<int|string> as the database returns them
     */
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