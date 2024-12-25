<?php
declare(strict_types=1);


namespace ZxArt\Import\Tunes;

use structureManager;
use ZxArt\Import\Resolver;
use ZxArt\Tunes\Repositories\TunesRepository;
use zxMusicElement;

readonly final class TuneResolver
{
    public function __construct(
        private TunesRepository          $tunesRepository,
        private structureManager         $structureManager,
        private Resolver $resolver,
    )
    {

    }

    public function resolve(TuneLabel $tuneLabel, $matchTunesWithoutYear = false): ?zxMusicElement
    {
        if ($tuneLabel->title === null) {
            return null;
        }

        $entityIds = $this->tunesRepository->findTunesByTitle($tuneLabel->title);

        $entities = $entityIds ? array_map(fn(int $id) => $this->structureManager->getElementById($id), $entityIds) : [];
        $candidates = [];

        foreach ($entities as $entity) {
            $score = $this->calculateScoreForElement($entity, $tuneLabel, $matchTunesWithoutYear);
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

    private function calculateScoreForElement(zxMusicElement $tuneElement, TuneLabel $tuneLabel, bool $matchTunesWithoutYear): int
    {
        $score = 0;
        $tuneLabelHasYear = $tuneLabel->year !== null && $tuneLabel->year > 0;
        $tuneElementHasYear = $tuneElement->year !== null && $tuneElement->year > 0;
        if (!$matchTunesWithoutYear && !$tuneElementHasYear) {
            return 0;
        }
        $tuneElementTitle = mb_strtolower(trim(html_entity_decode($tuneElement->title)));
        $tuneLabelTitle = mb_strtolower(trim($tuneLabel->title));

        // exact title match
        if ($this->resolver->valueMatches($tuneElementTitle, $tuneLabelTitle)) {
            $score += 10;
        } // alphanumeric title match
        elseif ($this->resolver->alphanumericValueMatches($tuneElementTitle, $tuneLabelTitle)) {
            $score += 5;
        } // alphanumeric title match
        elseif ($this->resolver->valueStartMatches($tuneElementTitle, $tuneLabelTitle)) {
            $score += 2;
        }

        // both years are not empty and match
        if ($this->resolver->intMatches((int)$tuneElement->year, $tuneLabel->year)) {
            $score += 10;
        }
        // neither one has year.
        if (!$tuneLabelHasYear && !$tuneElementHasYear) {
            $score += 1;
        }

        return $score;
    }


}