<?php

declare(strict_types=1);

namespace ZxArt\Parties\Services;

use partyElement;
use structureManager;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\PicturesTransformer;
use ZxArt\Pictures\Repositories\PicturesRepository;
use ZxArt\Prods\Exception\ProdDetailsException;
use zxPictureElement;

/**
 * Provides the party's picture entries for a single compo. The party page loads one compo per
 * request (lazily, per tab) rather than every compo at once.
 */
readonly class PartyPicturesService
{
    /**
     * Entries without a compo are exposed under this pseudo-compo (see partyElement::getCompos()),
     * while the compo column stores an empty string for them.
     */
    private const string COMPO_NONE = 'none';

    public function __construct(
        private structureManager $structureManager,
        private PicturesRepository $picturesRepository,
        private PicturesTransformer $picturesTransformer,
    ) {
    }

    /**
     * @return PictureDto[]
     */
    public function getPictures(int $partyId, string $compoType): array
    {
        $party = $this->structureManager->getElementById($partyId);
        if (!$party instanceof partyElement) {
            throw new ProdDetailsException('Party not found', 404);
        }

        $compo = $compoType === self::COMPO_NONE ? '' : $compoType;
        $pictureIds = $this->picturesRepository->findIdsByPartyAndCompo($partyId, $compo);

        $result = [];
        foreach ($pictureIds as $pictureId) {
            $picture = $this->structureManager->getElementById($pictureId);
            if ($picture instanceof zxPictureElement) {
                $result[] = $this->picturesTransformer->toDto($picture);
            }
        }

        return $result;
    }
}
