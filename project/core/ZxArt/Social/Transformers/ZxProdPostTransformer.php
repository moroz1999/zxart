<?php

declare(strict_types=1);

namespace ZxArt\Social\Transformers;

use ZxArt\Telegram\PostDto;
use zxProdElement;

readonly class ZxProdPostTransformer
{
    public function transform(zxProdElement $element): PostDto
    {
        return new PostDto(
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            link: (string)$element->getCanonicalUrl(),
            image: (string)$element->getImageUrl(0, 'telegramFull'),
            description: html_entity_decode((string)$element->getMetaDescription(), ENT_QUOTES),
        );
    }
}
