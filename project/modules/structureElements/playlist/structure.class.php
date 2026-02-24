<?php

class playlistElement extends structureElement
{
    public $dataResourceName = 'module_playlist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $picturesList;
    protected $musicList;
    protected $zxProdsList;
    private $connectedElements;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['userId'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
    }

    /**
     * @return (int|mixed)[]
     *
     * @psalm-return array{id: int, title: mixed, url: mixed}
     */
    public function getElementData(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->title,
            'url' => $this->URL,
        ];
    }

    /**
     * @psalm-param 'zxMusic'|'zxPicture'|'zxProd' $structureType
     *
     * @psalm-return list<mixed>
     */
    private function getStructuredList(string $structureType): array
    {
        if (!isset($this->connectedElements)) {
            $this->connectedElements = [];
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            /**
             * @var linksManager $linksManager
             */
            $linksManager = $this->getService(linksManager::class);
            $elementsIds = $linksManager->getConnectedIdList($this->getId(), 'playlist', 'parent');
            $this->connectedElements = $structureManager->getElementsByIdList($elementsIds);
        }
        $targetList = [];
        foreach ($this->connectedElements as $element) {
            if ($element->structureType == $structureType) {
                $targetList[] = $element;
            }
        }
        return $targetList;
    }

    public function getZxProdsList()
    {
        if ($this->zxProdsList === null) {
            $this->zxProdsList = $this->getStructuredList('zxProd');
        }
        return $this->zxProdsList;
    }

    public function getPicturesList()
    {
        if ($this->picturesList === null) {
            $this->picturesList = $this->getStructuredList('zxPicture');
        }
        return $this->picturesList;
    }

    public function getMusicList()
    {
        if ($this->musicList === null) {
            $this->musicList = $this->getStructuredList('zxMusic');
        }
        return $this->musicList;
    }

    /**
     * @return false|string
     */
    public function getZxProdsListData(): string|false
    {
        $prods = $this->getZxProdsList();
        $data = [
            'prods' => [],
            'prodsAmount' => count($prods)
        ];
        foreach ($prods as $prod) {
            $data['prods'][] = $prod->getElementData('list');
        }
        return json_encode($data);
    }
}


