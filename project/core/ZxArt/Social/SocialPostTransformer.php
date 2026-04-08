<?php

declare(strict_types=1);

namespace ZxArt\Social;

use structureElement;
use ZxArt\Social\Transformers\ZxMusicPostTransformer;
use ZxArt\Social\Transformers\ZxPicturePostTransformer;
use ZxArt\Social\Transformers\ZxProdPostTransformer;
use ZxArt\Social\Transformers\ZxReleasePostTransformer;
use ZxArt\Telegram\PostDto;
use zxMusicElement;
use zxPictureElement;
use zxProdElement;
use zxReleaseElement;

readonly class SocialPostTransformer
{
    public function __construct(
        private ZxMusicPostTransformer $musicTransformer,
        private ZxPicturePostTransformer $pictureTransformer,
        private ZxProdPostTransformer $prodTransformer,
        private ZxReleasePostTransformer $releaseTransformer,
    ) {
    }

    public function transform(structureElement $element): ?PostDto
    {
        return match (true) {
            $element instanceof zxMusicElement => $this->musicTransformer->transform($element),
            $element instanceof zxPictureElement => $this->pictureTransformer->transform($element),
            $element instanceof zxProdElement => $this->prodTransformer->transform($element),
            $element instanceof zxReleaseElement => $this->releaseTransformer->transform($element),
            default => null,
        };
    }
}
