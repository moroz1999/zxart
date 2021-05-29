<?php

/**
 * Trait PublisherProdsProvider
 */
trait PublisherProdsProvider
{
    protected $publisherProds;

    public function getPublisherProdsJson()
    {
        $prods = $this->getPublisherProds();
        $data = [
            'prods' => [],
            'prodsAmount' => count($prods)
        ];
        foreach ($prods as $prod) {
            $data['prods'][] = $prod->getElementData('list');
        }
        return json_encode($data);
    }

    public function getPublisherProds()
    {
        if ($this->publisherProds === null) {
            $this->publisherProds = [];
            /**
             * @var linksManager $linksManager
             */
            $linksManager = $this->getService('linksManager');
            if ($prodIds = $linksManager->getConnectedIdList($this->id, 'zxProdPublishers', 'parent')) {
                $structureManager = $this->getService('structureManager');
                foreach ($prodIds as $prodId) {
                    if ($prodElement = $structureManager->getElementById($prodId)) {
                        $this->publisherProds[] = $prodElement;
                    }
                }
            }
            if ($this instanceof AliasesHolder) {
                if ($aliasElements = $this->getAliasElements()) {
                    foreach ($aliasElements as $aliasElement) {
                        if ($prods = $aliasElement->getPublisherProds()) {
                            foreach ($prods as $prodElement) {
                                $this->publisherProds[] = $prodElement;
                            }
                        }
                    }
                }
            }
            $sort = [];
            foreach ($this->publisherProds as $prod) {
                $sort[] = trim($prod->getHumanReadableName());
            }
            array_multisort($sort, SORT_ASC, $this->publisherProds);
        }

        return $this->publisherProds;
    }
}