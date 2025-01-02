<?php
declare(strict_types=1);


namespace ZxArt\Import\Pictures;

use structureManager;
use ZxArt\Import\Resolver;
use ZxArt\Pictures\Repositories\PicturesRepository;
use zxPictureElement;

readonly final class PictureResolver
{
    public function __construct(
        private PicturesRepository $picturesRepository,
        private structureManager   $structureManager,
        private Resolver           $resolver,
    )
    {

    }

    public function resolve(PictureLabel $pictureLabel, $matchPicturesWithoutYear = false): ?zxPictureElement
    {
        if ($pictureLabel->title === null) {
            return null;
        }

        $entityIds = $this->picturesRepository->findPicturesByTitle($pictureLabel->title);

        $entities = $entityIds ? array_map(fn(int $id) => $this->structureManager->getElementById($id), $entityIds) : [];
        $candidates = [];

        foreach ($entities as $entity) {
            if ($entity === null) {
                continue;
            }
            $score = $this->calculateScoreForElement($entity, $pictureLabel, $matchPicturesWithoutYear);
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

    private function calculateScoreForElement(zxPictureElement $pictureElement, PictureLabel $pictureLabel, bool $matchPicturesWithoutYear): int
    {
        $score = 0;
        $pictureLabelHasYear = $pictureLabel->year !== null && $pictureLabel->year > 0;
        $pictureElementHasYear = $pictureElement->year !== null && $pictureElement->year > 0;
        if (!$matchPicturesWithoutYear && !$pictureElementHasYear) {
            return 0;
        }
        $pictureElementTitle = mb_strtolower(trim(html_entity_decode($pictureElement->title)));
        $pictureLabelTitle = mb_strtolower(trim($pictureLabel->title));

        // exact title match
        if ($this->resolver->valueMatches($pictureElementTitle, $pictureLabelTitle)) {
            $score += 10;
        } // alphanumeric title match
        elseif ($this->resolver->alphanumericValueMatches($pictureElementTitle, $pictureLabelTitle)) {
            $score += 5;
        } // partial start title match
        elseif ($this->resolver->valueStartsWith($pictureElementTitle, $pictureLabelTitle)) {
            $score += 2;
        }

        // both years are not empty and match
        if ($this->resolver->intMatches((int)$pictureElement->year, $pictureLabel->year)) {
            $score += 10;
        }
        // neither one has year.
        if (!$pictureLabelHasYear && !$pictureElementHasYear) {
            $score += 1;
        }

        return $score;
    }


}