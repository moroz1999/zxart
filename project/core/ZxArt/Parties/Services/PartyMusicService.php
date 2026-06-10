<?php

declare(strict_types=1);

namespace ZxArt\Parties\Services;

use partyElement;
use structureManager;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;

/**
 * Provides the party's tune entries for a single compo. The party page loads one compo per request
 * (lazily, per tab) rather than every compo at once.
 */
readonly class PartyMusicService
{
    public function __construct(
        private structureManager $structureManager,
        private TunesTransformer $tunesTransformer,
    ) {
    }

    /**
     * @return TuneDto[]
     */
    public function getTunes(int $partyId, string $compoType): array
    {
        $party = $this->structureManager->getElementById($partyId);
        if (!$party instanceof partyElement) {
            throw new ProdDetailsException('Party not found', 404);
        }

        $compos = $party->getTunesCompos();
        $tunes = $compos[$compoType] ?? [];

        $result = [];
        foreach ($tunes as $tune) {
            if ($tune instanceof zxMusicElement) {
                $result[] = $this->tunesTransformer->toDto($tune);
            }
        }

        return $result;
    }
}
