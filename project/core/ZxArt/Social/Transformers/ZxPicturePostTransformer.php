<?php

declare(strict_types=1);

namespace ZxArt\Social\Transformers;

use ZxArt\Telegram\PostDto;
use zxPictureElement;

readonly class ZxPicturePostTransformer
{
    public function transform(zxPictureElement $element): PostDto
    {
        return new PostDto(
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            link: (string)$element->getCanonicalUrl(),
            image: $element->getImageUrl(3, false),
            description: html_entity_decode((string)$element->getTextContent(), ENT_QUOTES),
        );
    }
}
