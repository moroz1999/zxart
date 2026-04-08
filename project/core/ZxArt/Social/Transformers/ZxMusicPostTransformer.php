<?php

declare(strict_types=1);

namespace ZxArt\Social\Transformers;

use ZxArt\Telegram\PostDto;
use zxMusicElement;

readonly class ZxMusicPostTransformer
{
    public function transform(zxMusicElement $element): PostDto
    {
        $textContent = $element->getTextContent();
        $description = is_array($textContent) ? implode(' ', $textContent) : (string)$textContent;
        $audioUrl = $element->isPlayable() ? $element->getMp3FilePath() : null;

        return new PostDto(
            title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
            link: (string)$element->getCanonicalUrl(),
            image: null,
            description: html_entity_decode($description, ENT_QUOTES),
            audio: $audioUrl,
        );
    }
}
