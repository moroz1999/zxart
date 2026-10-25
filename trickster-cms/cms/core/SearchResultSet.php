<?php

class SearchResultSet
{
    /** @var string */
    public $type = '';
    public $template = false;
    public $partial = false;
    protected $totalCount = 0;
    /** @var structureElement[] */
    public $elements = [];

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    public function getSubCount()
    {
        return false;
    }

    public function getJsonData()
    {
        $data = [
            'prods' => [],
            'prodsAmount' => count($this->elements)
        ];
        foreach ($this->elements as $element) {
            if ($element instanceof JsonDataProvider) {
                $data['prods'][] = $element->getElementData('list');
            }
        }
        return json_encode($data);
    }
}


