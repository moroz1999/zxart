<?php

declare(strict_types=1);

namespace ZxArt\Parties\Services;

use partyElement;
use structureManager;
use ZxArt\Parties\Dto\PartyOverviewDto;
use ZxArt\Pictures\PicturesTransformer;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\ProdsTransformer;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;
use zxPictureElement;
use zxProdElement;

/**
 * Builds the party overview ("best works"): the winning entry of each compo, grouped by medium.
 * The winner is the entry with the lowest positive party place (falling back to the first entry
 * when no place is set).
 */
readonly class PartyOverviewService
{
    public function __construct(
        private structureManager $structureManager,
        private ProdsTransformer $prodsTransformer,
        private PicturesTransformer $picturesTransformer,
        private TunesTransformer $tunesTransformer,
    ) {
    }

    public function getOverview(int $partyId): PartyOverviewDto
    {
        $party = $this->structureManager->getElementById($partyId);
        if (!$party instanceof partyElement) {
            throw new ProdDetailsException('Party not found', 404);
        }

        $prods = [];
        foreach ($party->getProdsCompos() as $entries) {
            $winner = $this->pickWinner($entries);
            if ($winner instanceof zxProdElement) {
                $prods[] = $this->prodsTransformer->toDto($winner);
            }
        }

        $pictures = [];
        foreach ($party->getPicturesCompos() as $entries) {
            $winner = $this->pickWinner($entries);
            if ($winner instanceof zxPictureElement) {
                $pictures[] = $this->picturesTransformer->toDto($winner);
            }
        }

        $tunes = [];
        foreach ($party->getTunesCompos() as $entries) {
            $winner = $this->pickWinner($entries);
            if ($winner instanceof zxMusicElement) {
                $tunes[] = $this->tunesTransformer->toDto($winner);
            }
        }

        return new PartyOverviewDto($prods, $pictures, $tunes);
    }

    /**
     * @param array<array-key, mixed> $entries
     */
    private function pickWinner(array $entries): mixed
    {
        $winner = null;
        $winnerPlace = null;
        foreach ($entries as $entry) {
            if ($winner === null) {
                $winner = $entry;
            }
            $place = (int)$entry->partyplace;
            if ($place > 0 && ($winnerPlace === null || $place < $winnerPlace)) {
                $winner = $entry;
                $winnerPlace = $place;
            }
        }
        return $winner;
    }
}
