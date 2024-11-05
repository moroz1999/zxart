<?php
declare(strict_types=1);


namespace ZxArt\Import\Prods;

use structureManager;
use ZxArt\Prods\Repositories\ProdsRepository;
use zxProdElement;

readonly final class ProdResolver
{
    public function __construct(
        private ProdsRepository  $prodsRepository,
        private structureManager $structureManager,
    )
    {

    }

    public function resolve(Prod $prod, $matchProdsWithoutYear = false): ?zxProdElement
    {
        /**
         * @var zxProdElement $element
         */
        $prodHasYear = $prod->year !== null && $prod->year > 0;
        if ($prodHasYear) {
            $id = $this->prodsRepository->getProdByTitleAndYear($prod->title, $prod->year);
            $element = $this->structureManager->getElementById($id);
            if ($element) {
                return $element;
            }
        }
        // there is no prod with such year (or no year provided)
        if (!$prodHasYear || $matchProdsWithoutYear) {
            $id = $this->prodsRepository->getProdByTitle($prod->title);
            $element = $this->structureManager->getElementById($id);
        }

        return $element;
    }
}