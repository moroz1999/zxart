<?php

declare(strict_types=1);

namespace ZxArt\Social;

use structureElement;
use ZxArt\Telegram\PostDto;
use zxMusicElement;
use zxPictureElement;
use zxProdElement;
use zxReleaseElement;

readonly class SocialPostTransformer
{
    public function transform(structureElement $element): ?PostDto
    {
        if ($element instanceof zxReleaseElement) {
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: (string)$element->getImageUrl(0),
                description: html_entity_decode($element->getTextContent(), ENT_QUOTES),
            );
        }
        if ($element instanceof zxMusicElement) {
            $description = $element->getTextContent();
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: null,
                description: html_entity_decode($description, ENT_QUOTES),
            );
        }
        if ($element instanceof zxPictureElement) {
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: $element->getImageUrl(3, false),
                description: html_entity_decode($element->getTextContent(), ENT_QUOTES),
            );
        }
        if ($element instanceof zxProdElement) {
            $description = $element->getMetaDescription();
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: (string)$element->getImageUrl(0),
                description: html_entity_decode($description, ENT_QUOTES),
            );
        }

        return null;
    }
}
