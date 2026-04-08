<?php

declare(strict_types=1);

namespace ZxArt\Social\Transformers;

use ZxArt\Telegram\PostDto;
use zxReleaseElement;

readonly class ZxReleasePostTransformer
{
    public function transform(zxReleaseElement $element): PostDto
    {
        return new PostDto(
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            link: (string)$element->getCanonicalUrl(),
            image: (string)$element->getImageUrl(0, 'telegramFull'),
            description: html_entity_decode((string)$element->getTextContent(), ENT_QUOTES),
        );
    }
}
