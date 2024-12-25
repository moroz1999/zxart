<?php
declare(strict_types=1);


namespace ZxArt\Import\Prods;

use structureManager;
use ZxArt\Import\Resolver;
use ZxArt\Prods\Repositories\ProdsRepository;
use zxProdElement;

readonly final class ProdResolver
{
    public function __construct(
        private ProdsRepository          $prodsRepository,
        private structureManager         $structureManager,
        private Resolver $resolver,
    )
    {

    }

    public function resolve(ProdLabel $prodLabel, $matchProdsWithoutYear = false): ?zxProdElement
    {
        if ($prodLabel->title === null) {
            return null;
        }

        $entityIds = $this->prodsRepository->findProdsByTitle($prodLabel->title);

        $entities = $entityIds ? array_map(fn(int $id) => $this->structureManager->getElementById($id), $entityIds) : [];
        $candidates = [];

        foreach ($entities as $entity) {
            $score = $this->calculateScoreForElement($entity, $prodLabel, $matchProdsWithoutYear);
            if ($score <= 0) {
                continue;
            }
            $candidates[] = [
                'element' => $entity,
                'score' => $score,
            ];
        }

        usort($candidates, static fn($a, $b) => $b['score'] <=> $a['score']);

        return $candidates[0]['element'] ?? null;
    }

    private function calculateScoreForElement(zxProdElement $prodElement, ProdLabel $prodLabel, bool $matchProdsWithoutYear): int
    {
        $score = 0;
        $prodLabelHasYear = $prodLabel->year !== null && $prodLabel->year > 0;
        $prodElementHasYear = $prodElement->year !== null && $prodElement->year > 0;
        if (!$matchProdsWithoutYear && !$prodElementHasYear) {
            return 0;
        }
        $prodElementTitle = mb_strtolower(trim(html_entity_decode($prodElement->title)));
        $prodLabelTitle = mb_strtolower(trim($prodLabel->title));

        // exact title match
        if ($this->resolver->valueMatches($prodElementTitle, $prodLabelTitle)) {
            $score += 10;
        } // alphanumeric title match
        elseif ($this->resolver->alphanumericValueMatches($prodElementTitle, $prodLabelTitle)) {
            $score += 5;
        } // alphanumeric title match
        elseif ($this->resolver->valueStartMatches($prodElementTitle, $prodLabelTitle)) {
            $score += 2;
        }

        // both years are not empty and match
        if ($this->resolver->intMatches((int)$prodElement->year, $prodLabel->year)) {
            $score += 10;
        }
        // neither one has year.
        if (!$prodLabelHasYear && !$prodElementHasYear) {
            $score += 1;
        }

        return $score;
    }
}