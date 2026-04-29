<?php

trait SearchTypesProviderTrait
{
    protected $searchTypes;
    protected $searchTypesString;

    /**
     * @param string $set
     * @return array
     */
    public function getSearchTypes($set = 'public')
    {
        if ($this->searchTypes === null) {
            $this->searchTypes = $this->getService(ConfigManager::class)
                ->getMerged('searchtypes-' . $set . '.' . $this->structureType);
        }
        return $this->searchTypes;
    }

    /**
     * @param string $set
     * @return string
     */
    public function getSearchTypesString($set = 'public')
    {
        if ($this->searchTypesString === null) {
            $this->searchTypesString = implode(',', $this->getSearchTypes($set));
        }
        return $this->searchTypesString;
    }
}