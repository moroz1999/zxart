<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Tunes\Dto\TuneDto;

/**
 * Winners of the party — the top entry of each compo, grouped by medium. Each medium uses its shared
 * transformer DTO; the controller maps them to the matching REST DTOs.
 */
readonly class PartyOverviewDto
{
    /**
     * @param ProdDto[] $prods
     * @param PictureDto[] $pictures
     * @param TuneDto[] $tunes
     */
    public function __construct(
        public array $prods,
        public array $pictures,
        public array $tunes,
    ) {
    }
}
