<?php
declare(strict_types=1);


namespace ZxArt\Import\Parties;

use partyElement;
use ZxArt\Parties\Services\PartiesService;

readonly final class PartyResolver
{
    public function __construct(
        private PartiesService   $partiesService,
    )
    {

    }

    public function resolve(Party $party): ?partyElement
    {
        /**
         * @var zxPartyElement $element
         */
        $partyHasYear = $party->year !== null && $party->year > 0;
        if (!$partyHasYear) {
            return null;
        }
        return $this->partiesService->getPartyByTitleAndYear($party->title, $party->year);
    }
}