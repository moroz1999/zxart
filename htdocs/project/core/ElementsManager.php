<?php

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

class ElementsManager extends errorLogger
{
    protected structureManager $structureManager;
    protected array $elementsIndex = [];

    protected Connection $db;

    public function setDb(Connection $db)
    {
        $this->db = $db;
    }

    public function getElementsByQuery(?Builder $query, ?array $sort = [], ?int $start = null, ?int $amount = null)
    {
        return $this->loadElements($query, $sort, $start, $amount);
    }

    protected function loadElements(?Builder $query, ?array $sort = [], ?int $start = null, ?int $amount = null)
    {
        if ($query === null) {
            $query = $this->db->table(static::TABLE);
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
        if ($start !== null) {
            $query->offset($start);
        }
        if ($amount !== null) {
            $query->limit($amount);
        }

        if ($records = $query->select($query->from . '.id')->get()) {
            $idList = array_column($records, 'id');
            $result = $this->structureManager->getElementsByIdList($idList);
            foreach ($result as $element) {
                $this->elementsIndex[$element->id] = $element;
            }
        }

        return $result;
    }

    public function setStructureManager(structureManager $structureManager)
    {
        $this->structureManager = $structureManager;
    }

    protected function manufactureElement($id)
    {
        $result = false;
        if ($structureElement = $this->structureManager->getElementById($id)) {
            $result = $structureElement;
        }
        return $result;
    }
}