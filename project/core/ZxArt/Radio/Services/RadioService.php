<?php

declare(strict_types=1);

namespace ZxArt\Radio\Services;

use structureManager;
use ZxArt\Radio\Dto\RadioCriteriaDto;
use ZxArt\Radio\Exception\RadioTuneNotFoundException;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Repositories\TunesRepository;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;
use zxProdCategoryElement;

readonly class RadioService
{
    public function __construct(
        private structureManager $structureManager,
        private TunesRepository $tunesRepository,
        private TunesTransformer $tunesTransformer,
    ) {
    }

    /**
     * @throws RadioTuneNotFoundException
     */
    public function getNextTune(RadioCriteriaDto $criteria): TuneDto
    {
        $normalizedCriteria = $this->expandCategories($criteria);
        $id = $this->tunesRepository->findRandomIdByCriteria($normalizedCriteria);
        if ($id === null) {
            throw RadioTuneNotFoundException::forCriteria();
        }

        $element = $this->structureManager->getElementById($id);
        if (!$element instanceof zxMusicElement) {
            throw RadioTuneNotFoundException::forId($id);
        }

        return $this->tunesTransformer->toDto($element);
    }

    private function expandCategories(RadioCriteriaDto $criteria): RadioCriteriaDto
    {
        if ($criteria->prodCategoriesInclude === []) {
            return $criteria;
        }

        $expanded = [];
        foreach ($criteria->prodCategoriesInclude as $categoryId) {
            $element = $this->structureManager->getElementById($categoryId);
            if ($element instanceof zxProdCategoryElement) {
                $ids = [];
                $element->getSubCategoriesTreeIds($ids);
                $expanded = array_merge($expanded, $ids);
                continue;
            }
            $expanded[] = $categoryId;
        }

        $expanded = array_values(array_unique($expanded));

        return new RadioCriteriaDto(
            minRating: $criteria->minRating,
            maxRating: $criteria->maxRating,
            yearsInclude: $criteria->yearsInclude,
            yearsExclude: $criteria->yearsExclude,
            countriesInclude: $criteria->countriesInclude,
            countriesExclude: $criteria->countriesExclude,
            formatGroupsInclude: $criteria->formatGroupsInclude,
            formatGroupsExclude: $criteria->formatGroupsExclude,
            formatsInclude: $criteria->formatsInclude,
            formatsExclude: $criteria->formatsExclude,
            prodCategoriesInclude: $expanded,
            bestVotesLimit: $criteria->bestVotesLimit,
            maxPlays: $criteria->maxPlays,
            minPartyPlace: $criteria->minPartyPlace,
            requireGame: $criteria->requireGame,
            hasParty: $criteria->hasParty,
            notVotedByUserId: $criteria->notVotedByUserId,
        );
    }
}
