<?php

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

class ElementsManager extends errorLogger
{
    protected structureManager $structureManager;
    protected LanguagesManager $languagesManager;
    protected array $elementsIndex = [];

    protected Connection $db;

    /**
     * @param LanguagesManager $languagesManager
     *
     * @return void
     */
    public function setLanguagesManager(LanguagesManager $languagesManager)
    {
        $this->languagesManager = $languagesManager;
    }

    public function setDb(Connection $db): void
    {
        $this->db = $db;
    }

    public function getElementsByQuery(?Builder $query, ?array $sort = [], ?int $start = null, ?int $amount = null)
    {
        return $this->loadElements($query, $sort, $start, $amount);
    }

    /**
     * @return structureElement[]
     *
     * @psalm-return array<structureElement>
     */
    protected function loadElements(?Builder $query, ?array $sort = [], ?int $start = null, ?int $amount = null): array
    {
        if ($query === null) {
            $query = $this->makeQuery();
        } else {
            $query = clone($query);
        }
        if (is_array($sort)) {
            foreach ($sort as $property => $order) {
                if ($order == 'rand' || $property == 'rand') {
                    $query->inRandomOrder();
                    break;
                }
                if (isset($this->columnRelations[$property])) {
                    foreach ($this->columnRelations[$property] as $criteria => $orderDirection) {
                        if ($criteria == 'dateCreated') {
                            $query->leftJoin('structure_elements', 'structure_elements.id', '=', $query->from . '.id');
                            $query->orderBy("structure_elements.dateCreated", $orderDirection);
                        } else {
                            if ($orderDirection === true) {
                                $query->orderByRaw("$criteria $order");
                            } else {
                                if ($orderDirection === false) {
                                    if ($order == 'desc') {
                                        $query->orderByRaw("$criteria asc");
                                    } else {
                                        $query->orderByRaw("$criteria desc");
                                    }
                                } else {
                                    $query->orderByRaw("$criteria $orderDirection");
                                }
                            }
                        }
                    }
                }
            }
        }
        $result = [];
        if ($start !== null && $start > 0) {
            $query->offset($start);
        }
        if ($amount !== null) {
            $query->limit($amount);
        }

        if ($records = $query->select($query->from . '.id')->get()) {
            $idList = array_column($records, 'id');
            $result = $this->structureManager->getElementsByIdList($idList, $this->languagesManager->getCurrentLanguageId());
            foreach ($result as $element) {
                $this->elementsIndex[$element->id] = $element;
            }
        }

        return $result;
    }

    public function makeQuery(): Builder
    {
        return $this->db->table(static::TABLE);
    }

    public function setStructureManager(structureManager $structureManager): void
    {
        $this->structureManager = $structureManager;
    }

    protected function manufactureElement($id): bool|structureElement
    {
        $result = false;
        if ($structureElement = $this->structureManager->getElementById($id)) {
            $result = $structureElement;
        }
        return $result;
    }
}